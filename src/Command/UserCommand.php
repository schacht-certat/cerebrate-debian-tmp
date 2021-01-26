<?php
namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Utility\Security;

class UserCommand extends Command
{
    protected $modelClass = 'Users';

    public function execute(Arguments $args, ConsoleIo $io)
    {
        $exit = false;
        while (!$exit) {
            $menu = [
                ['', 'Cerebrate users'],
                ['1', 'List users'],
                ['2', 'Reset password for a user'],
                ['0', 'Exit']
            ];
            $io->helper('Table')->output($menu);
            $choice = $io->ask('What would you like to do?');
            switch ($choice) {
                case '1':
                    $io->helper('Table')->output($this->listUsers());
                    break;
                case '2':
                    $user = $io->ask('Which user do you want to reset?');
                    $user = $this->selectUser($user);
                    if (empty($user)) {
                        $io->out('Invalid user.');
                    } else {
                        $automatic = $io->ask(sprintf('Would you like to generate a password automatically for user "%s"? (y/n)', $user['username']));
                        if ($automatic === 'y') {
                            $password = $this->generatePassword();
                        } elseif ($automatic === 'n') {
                            $password = $io->ask('Please enter the desired password:');
                        }
                        if (!empty($password)) {
                            if ($this->setPassword($user, $password)) {
                                $io->out(sprintf('Password reset for user "%s". The new password is: "%s"', $user['username'], $password));
                            } else {
                                $io->out('Could not save the provided password. Are you sure it meets the requirements?');
                            }
                        } else {
                            $io->out('Password empty, change aborted.');
                        }
                    }
                    break;
                case '0':
                    $exit = true;
                    break;
                default:
                    $io->out('Invalid selection');
                    break;
            }
        }
        $io->out('Goodbye!');
    }

    private function generatePassword()
    {
        return Security::randomString(16);
    }

    private function listUsers()
    {
        $users = $this->Users->find()->contain(['Individuals'])->all();
        $list = [['ID', 'Username', 'Email']];
        foreach ($users as $user) {
            $list[] = [
                (string)$user['id'], $user['username'], $user['individual']['email']
            ];
        }
        return $list;
    }

    private function selectUser($user)
    {
        if (is_numeric($user)) {
            $condition = ['id' => $user];
        } else {
            $condition = ['username' => $user];
        }
        $user = $this->Users->find()->where($condition)->first();
        return $user;
    }

    private function setPassword($user, $password)
    {
        $user->password = $password;
        return $this->Users->save($user);
    }
}
