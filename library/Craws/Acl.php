<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

namespace Craws;

class Acl extends \Zend_Acl {

    public function __construct() {

        $moduleSettings = \Zend_Registry::get('moduleSettings');

        $this->addRole(new \Zend_Acl_Role('guest'));
        $this->addRole(new \Zend_Acl_Role('readonly'), ('guest'));
        $this->addRole(new \Zend_Acl_Role('editor'), 'readonly');
        $this->addRole(new \Zend_Acl_Role('manager'), 'editor');
        $this->addRole(new \Zend_Acl_Role('admin'), 'manager');

        $this->add(new \Zend_Acl_Resource('admin:actor:add'));
        $this->add(new \Zend_Acl_Resource('admin:actor:delete'));
        $this->add(new \Zend_Acl_Resource('admin:actor:index'));
        $this->add(new \Zend_Acl_Resource('admin:actor:insert'));
        $this->add(new \Zend_Acl_Resource('admin:actor:link'));
        $this->add(new \Zend_Acl_Resource('admin:actor:update'));
        $this->add(new \Zend_Acl_Resource('admin:actor:view'));

        $this->add(new \Zend_Acl_Resource('admin:biblio:insert'));
        $this->add(new \Zend_Acl_Resource('admin:biblio:update'));

        $this->add(new \Zend_Acl_Resource('admin:carrier:insert'));
        $this->add(new \Zend_Acl_Resource('admin:carrier:delete'));
        $this->add(new \Zend_Acl_Resource('admin:carrier:update'));
        $this->add(new \Zend_Acl_Resource('admin:carrier:view'));

        $this->add(new \Zend_Acl_Resource('admin:class:index'));
        $this->add(new \Zend_Acl_Resource('admin:class:view'));

        $this->add(new \Zend_Acl_Resource('admin:content:index'));
        $this->add(new \Zend_Acl_Resource('admin:content:insert'));
        $this->add(new \Zend_Acl_Resource('admin:content:update'));
        $this->add(new \Zend_Acl_Resource('admin:content:view'));

        $this->add(new \Zend_Acl_Resource('admin:event:add'));
        $this->add(new \Zend_Acl_Resource('admin:event:delete'));
        $this->add(new \Zend_Acl_Resource('admin:event:index'));
        $this->add(new \Zend_Acl_Resource('admin:event:insert'));
        $this->add(new \Zend_Acl_Resource('admin:event:link'));
        $this->add(new \Zend_Acl_Resource('admin:event:update'));
        $this->add(new \Zend_Acl_Resource('admin:event:view'));

        $this->add(new \Zend_Acl_Resource('admin:faq:index'));

        $this->add(new \Zend_Acl_Resource('admin:function:add-field'));
        $this->add(new \Zend_Acl_Resource('admin:function:bookmark'));
        $this->add(new \Zend_Acl_Resource('admin:function:unlink'));

        $this->add(new \Zend_Acl_Resource('admin:hierarchy:delete'));
        $this->add(new \Zend_Acl_Resource('admin:hierarchy:index'));
        $this->add(new \Zend_Acl_Resource('admin:hierarchy:insert'));
        $this->add(new \Zend_Acl_Resource('admin:hierarchy:update'));
        $this->add(new \Zend_Acl_Resource('admin:hierarchy:view'));

        $this->add(new \Zend_Acl_Resource('admin:index:index'));
        $this->add(new \Zend_Acl_Resource('admin:index:logout'));

        $this->add(new \Zend_Acl_Resource('admin:involvement:insert'));
        $this->add(new \Zend_Acl_Resource('admin:involvement:update'));

        $this->add(new \Zend_Acl_Resource('admin:log:index'));
        $this->add(new \Zend_Acl_Resource('admin:log:delete'));
        $this->add(new \Zend_Acl_Resource('admin:log:delete-all'));
        $this->add(new \Zend_Acl_Resource('admin:log:view'));

        $this->add(new \Zend_Acl_Resource('admin:member:insert'));
        $this->add(new \Zend_Acl_Resource('admin:member:member'));
        $this->add(new \Zend_Acl_Resource('admin:member:update'));

        $this->add(new \Zend_Acl_Resource('admin:overview:changelog'));
        $this->add(new \Zend_Acl_Resource('admin:overview:credits'));
        $this->add(new \Zend_Acl_Resource('admin:overview:feedback'));
        $this->add(new \Zend_Acl_Resource('admin:overview:index'));
        $this->add(new \Zend_Acl_Resource('admin:overview:model'));

        $this->add(new \Zend_Acl_Resource('admin:place:add'));
        $this->add(new \Zend_Acl_Resource('admin:place:delete'));
        $this->add(new \Zend_Acl_Resource('admin:place:delete-name'));
        $this->add(new \Zend_Acl_Resource('admin:place:index'));
        $this->add(new \Zend_Acl_Resource('admin:place:insert'));
        $this->add(new \Zend_Acl_Resource('admin:place:link'));
        $this->add(new \Zend_Acl_Resource('admin:place:update'));
        $this->add(new \Zend_Acl_Resource('admin:place:view'));

        $this->add(new \Zend_Acl_Resource('admin:profile:index'));
        $this->add(new \Zend_Acl_Resource('admin:profile:password'));
        $this->add(new \Zend_Acl_Resource('admin:profile:update'));

        $this->add(new \Zend_Acl_Resource('admin:property:index'));
        $this->add(new \Zend_Acl_Resource('admin:property:view'));

        $this->add(new \Zend_Acl_Resource('admin:reference:delete'));
        $this->add(new \Zend_Acl_Resource('admin:reference:index'));
        $this->add(new \Zend_Acl_Resource('admin:reference:insert'));
        $this->add(new \Zend_Acl_Resource('admin:reference:link'));
        $this->add(new \Zend_Acl_Resource('admin:reference:update'));
        $this->add(new \Zend_Acl_Resource('admin:reference:view'));

        $this->add(new \Zend_Acl_Resource('admin:relation:insert'));
        $this->add(new \Zend_Acl_Resource('admin:relation:update'));

        $this->add(new \Zend_Acl_Resource('admin:search:index'));

        $this->add(new \Zend_Acl_Resource('admin:settings:index'));
        $this->add(new \Zend_Acl_Resource('admin:settings:update'));

        $this->add(new \Zend_Acl_Resource('admin:source:add'));
        $this->add(new \Zend_Acl_Resource('admin:source:delete'));
        $this->add(new \Zend_Acl_Resource('admin:source:index'));
        $this->add(new \Zend_Acl_Resource('admin:source:insert'));
        $this->add(new \Zend_Acl_Resource('admin:source:link'));
        $this->add(new \Zend_Acl_Resource('admin:source:text-add'));
        $this->add(new \Zend_Acl_Resource('admin:source:text-delete'));
        $this->add(new \Zend_Acl_Resource('admin:source:text-update'));
        $this->add(new \Zend_Acl_Resource('admin:source:update'));
        $this->add(new \Zend_Acl_Resource('admin:source:view'));

        $this->add(new \Zend_Acl_Resource('admin:user:delete'));
        $this->add(new \Zend_Acl_Resource('admin:user:index'));
        $this->add(new \Zend_Acl_Resource('admin:user:insert'));
        $this->add(new \Zend_Acl_Resource('admin:user:update'));
        $this->add(new \Zend_Acl_Resource('admin:user:view'));

        $this->add(new \Zend_Acl_Resource('default:contact:index'));
        $this->add(new \Zend_Acl_Resource('default:error:error'));
        $this->add(new \Zend_Acl_Resource('default:file:view'));
        $this->add(new \Zend_Acl_Resource('default:index:index'));
        $this->add(new \Zend_Acl_Resource('default:offline:index'));

        /* guest (not logged in) */
        $this->allow('guest', 'default:contact:index');
        $this->allow('guest', 'default:error:error');
        $this->allow('guest', 'default:file:view');
        $this->allow('guest', 'default:index:index');
        $this->allow('guest', 'default:offline:index');

        $this->allow('guest', 'admin:index:index');
        $this->allow('guest', 'admin:index:logout');

        /* readonly */
        $this->allow('readonly', 'admin:actor:index');
        $this->allow('readonly', 'admin:actor:view');

        $this->allow('readonly', 'admin:carrier:view');

        $this->allow('readonly', 'admin:class:index');
        $this->allow('readonly', 'admin:class:view');

        $this->allow('readonly', 'admin:event:index');
        $this->allow('readonly', 'admin:event:view');

        $this->allow('readonly', 'admin:faq:index');

        $this->allow('readonly', 'admin:hierarchy:index');
        $this->allow('readonly', 'admin:hierarchy:view');

        $this->allow('readonly', 'admin:overview:changelog');
        $this->allow('readonly', 'admin:overview:credits');
        $this->allow('readonly', 'admin:overview:feedback');
        $this->allow('readonly', 'admin:overview:index');
        $this->allow('readonly', 'admin:overview:model');

        $this->allow('readonly', 'admin:place:index');
        $this->allow('readonly', 'admin:place:view');

        $this->allow('readonly', 'admin:profile:index');

        $this->allow('readonly', 'admin:profile:password');
        $this->allow('readonly', 'admin:profile:update');

        $this->allow('readonly', 'admin:property:index');
        $this->allow('readonly', 'admin:property:view');

        $this->allow('readonly', 'admin:reference:index');
        $this->allow('readonly', 'admin:reference:view');

        $this->allow('readonly', 'admin:search:index');

        $this->allow('readonly', 'admin:source:index');
        $this->allow('readonly', 'admin:source:view');


        /* editor */
        $this->allow('editor', 'admin:actor:add');
        $this->allow('editor', 'admin:actor:delete');
        $this->allow('editor', 'admin:actor:insert');
        $this->allow('editor', 'admin:actor:link');
        $this->allow('editor', 'admin:actor:update');

        $this->allow('editor', 'admin:biblio:insert');
        $this->allow('editor', 'admin:biblio:update');

        $this->allow('editor', 'admin:carrier:insert');
        $this->allow('editor', 'admin:carrier:delete');
        $this->allow('editor', 'admin:carrier:update');

        $this->allow('editor', 'admin:event:add');
        $this->allow('editor', 'admin:event:delete');
        $this->allow('editor', 'admin:event:insert');
        $this->allow('editor', 'admin:event:link');
        $this->allow('editor', 'admin:event:update');

        $this->allow('editor', 'admin:function:add-field');
        $this->allow('editor', 'admin:function:bookmark');
        $this->allow('editor', 'admin:function:unlink');

        $this->allow('editor', 'admin:hierarchy:delete');
        $this->allow('editor', 'admin:hierarchy:insert');
        $this->allow('editor', 'admin:hierarchy:update');

        $this->allow('editor', 'admin:involvement:insert');
        $this->allow('editor', 'admin:involvement:update');

        $this->allow('editor', 'admin:member:insert');
        $this->allow('editor', 'admin:member:member');
        $this->allow('editor', 'admin:member:update');



        $this->allow('editor', 'admin:place:add');
        $this->allow('editor', 'admin:place:delete');
        $this->allow('editor', 'admin:place:delete-name');
        $this->allow('editor', 'admin:place:insert');
        $this->allow('editor', 'admin:place:link');
        $this->allow('editor', 'admin:place:update');

        $this->allow('editor', 'admin:reference:delete');
        $this->allow('editor', 'admin:reference:insert');
        $this->allow('editor', 'admin:reference:link');
        $this->allow('editor', 'admin:reference:update');

        $this->allow('editor', 'admin:relation:insert');
        $this->allow('editor', 'admin:relation:update');

        $this->allow('editor', 'admin:source:add');
        $this->allow('editor', 'admin:source:delete');
        $this->allow('editor', 'admin:source:insert');
        $this->allow('editor', 'admin:source:link');
        $this->allow('editor', 'admin:source:text-add');
        $this->allow('editor', 'admin:source:text-delete');
        $this->allow('editor', 'admin:source:text-update');
        $this->allow('editor', 'admin:source:update');

        /* manager */
        $this->allow('manager', 'admin:user:delete');
        $this->allow('manager', 'admin:user:index');
        $this->allow('manager', 'admin:user:insert');
        $this->allow('manager', 'admin:user:update');
        $this->allow('manager', 'admin:user:view');

        $this->allow('manager', 'admin:content:index');
        $this->allow('manager', 'admin:content:insert');
        $this->allow('manager', 'admin:content:update');
        $this->allow('manager', 'admin:content:view');

        /* admin */
        $this->allow('admin');

        // @codeCoverageIgnoreStart
        if ($moduleSettings['mail']) {
            $this->add(new \Zend_Acl_Resource('admin:index:reset-password'));
            $this->add(new \Zend_Acl_Resource('admin:index:reset-confirm'));
            $this->allow('guest', 'admin:index:reset-password');
            $this->allow('guest', 'admin:index:reset-confirm');
        }
        // @codeCoverageIgnoreEnd
    }

}
