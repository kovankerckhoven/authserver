<?php

namespace App\Entity;

use App\Doctrine\EntityRepository;
use App\Search\SearchValueException;

class GroupRepository extends EntityRepository
{
    public function find($id)
    {
        if (is_array($id)||is_int($id)||is_numeric($id)) {
            return parent::find($id);
        } else {
            return $this->findOneBy(array('name'=>$id));
        }
    }

    protected $fieldSearchWhitelist = array();

    public function handleUnknownSearchField(array &$block)
    {
        switch ($block['name']) {
            case 'name':
                $block['name'] = 'displayName';
                break;
            case 'techname':
                $block['name'] = 'name';
                break;
            case 'is':
                switch (strtolower($block['value'])) {
                    case 'exportable':
                        $block['name']  = 'exportable';
                        $block['value'] = '1';
                        break;
                    case 'not exportable':
                    case 'noexportable':
                        $block['name'] = 'exportable';
                        $block['value'] = '0';
                        break;
                    case 'nogroups':
                        $block['name'] = 'noGroups';
                        $block['value'] = '1';
                        break;
                    case 'groups':
                        $block['name'] = 'noGroups';
                        $block['value'] = '0';
                        break;
                    case 'nousers':
                        $block['name'] = 'noUsers';
                        $block['value'] = '1';
                        break;
                    case 'users':
                        $block['name'] = 'noUsers';
                        $block['value'] = '0';
                        break;
                    default:
                        throw new SearchValueException($block['name'], $block['value'], array('exportable', 'not exportable', 'nogroups', 'groups', 'nousers', 'users'));
                }
                break;
            default:
                parent::handleUnknownSearchField($block);
        }
    }
}
