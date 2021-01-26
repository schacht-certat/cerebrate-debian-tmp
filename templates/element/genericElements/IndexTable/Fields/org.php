<?php
    $orgs = $row[$field['data_path']];
    if (!isset($field['fields']['allow_picture'])) {
        $field['fields']['allow_picture'] = true;
    }
    if (!isset($field['fields']['default_org'])) {
        $field['fields']['default_org'] = '';
    }
    if (!empty($orgs)) {
        if (!empty($orgs['id'])) {
            $orgs = [$orgs];
        }
        $count = count($orgs);
        $i = 0;
        foreach ($orgs as $org) {
            $i++;
            if (!empty($org['id']) || !empty($org['name'])) {
                if ($field['fields']['allow_picture'] && !empty($org['id'])) {
                    echo sprintf(
                        '<a href="%s">%s</a>',
                        $baseurl . '/organisations/view/' . h($org['id']),
                        h($org['name'])
                    );
                    //echo $this->OrgImg->getOrgImg(array('name' => $org['name'], 'id' => $org['id'], 'size' => 24));
                } else {
                    echo sprintf(
                        '<a href="%s/organisations/view/%s">%s</a>',
                        $baseurl,
                        empty($org['id']) ? h($org['uuid']) : h($org['id']),
                        h($org['name'])
                    );
                }
                if ($i < $count) {
                    echo '<br />';
                }
            } else {
                if ($field['fields']['allow_picture']) {
                    echo sprintf(
                        '<a href="%s">%s</a>',
                        $baseurl . '/organisations/view/' . h($org['id']),
                        h($org['name'])
                    );
                    //echo $this->OrgImg->getOrgImg(array('name' =>  $field['fields']['default_org'], 'size' => 24));
                }
            }
        }
    }
?>
