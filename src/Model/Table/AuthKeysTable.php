<?php

namespace App\Model\Table;

use App\Model\Table\AppTable;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventInterface;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Utility\Security;
use Cake\Http\Exception\MethodNotAllowedException;
use ArrayObject;

class AuthKeysTable extends AppTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->addBehavior('UUID');
        $this->belongsTo(
            'Users'
        );
        $this->setDisplayField('authkey');
    }

    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options)
    {
        $data['created'] = time();
        if (empty($data['expiration'])) {
            $data['expiration'] = 0;
        } else {
            $data['expiration'] = strtotime($data['expiration']);
        }
    }

    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        if (empty($entity->authkey)) {
            $authkey = $this->generateAuthKey();
            $entity->authkey_start = substr($authkey, 0, 4);
            $entity->authkey_end = substr($authkey, -4);
            $entity->authkey = (new DefaultPasswordHasher())->hash($authkey);
            $entity->authkey_raw = $authkey;
        }
    }

    public function generateAuthKey()
    {
        return Security::randomString(40);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('user_id')
            ->requirePresence(['user_id'], 'create');
        return $validator;
    }

    public function checkKey($authkey)
    {
        if (strlen($authkey) != 40) {
            return [];
        }
        $start = substr($authkey, 0, 4);
        $end = substr($authkey, -4);
        $candidates = $this->find()->where([
            'authkey_start' => $start,
            'authkey_end' => $end,
            'OR' => [
                'expiration' => 0,
                'expiration >' => time()
            ]
        ]);
        if (!empty($candidates)) {
            foreach ($candidates as $candidate) {
                if ((new DefaultPasswordHasher())->check($authkey, $candidate['authkey'])) {
                    return $candidate;
                }
            }
        }
        return [];
    }
}
