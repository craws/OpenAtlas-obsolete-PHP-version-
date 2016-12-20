<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_HierarchyController extends Zend_Controller_Action {

    public function deleteAction() {
        $type = Model_NodeMapper::getById($this->_getParam('id'));
        if (!$type->extendable ||
            (!$type->superId && $type->system) ||
            (!$type->superId && !in_array(Zend_Registry::get('user')->group, ['admin', 'manager']))) {
            $this->_helper->message('error_forbidden');
            return $this->_helper->redirector->gotoUrl('/admin/hierarchy');
        }
        if (!empty($type->subs)) {
            $this->_helper->message('error_subs_exists');
            return $this->_helper->redirector->gotoUrl('/admin/hierarchy/view/id/' . $type->id);
        }
        if ($type->getLinks(['P2', 'P89'], true)) {
            $this->_helper->message('error_links_exists');
            return $this->_helper->redirector->gotoUrl('/admin/hierarchy/view/id/' . $type->id);
        }
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $type->delete();
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_delete');
        return $this->_helper->redirector->gotoUrl('/admin/hierarchy#tab' . $type->rootId);
    }

    private function walkTree($node) {
        $count = ($node->class->code == 'E53') ? $node->count - count($node->subs) : $node->count;
        $text = "{href: '/admin/hierarchy/view/id/" . $node->id . "',";
        $text .= "text: '" . str_replace("'", "\'", $node->name) . " (" . $count . ")', 'id':'" . $node->id . "'";
        if ($node->subs) {
            $text .= ",'children' : [";
            foreach ($node->subs as $sub) {
                $text .= self::walkTree($sub);
            }
            $text .= "]";
        }
        $text .= "},";
        return $text;
    }

    private function treeSelect($node) {
        $tree = "'core':{'data':[";
        foreach ($node->subs as $sub) {
            $tree .= self::walkTree($sub);
        }
        $tree .= "]}";
        $html = '<div id="' . $node->id . '-tree"></div>
            <script type="text/javascript">
                $(document).ready(function () {
                $("#' . $node->id . '-tree").jstree({
                    "search": {"case_insensitive": true, "show_only_matches": true},
                    "plugins" : ["core", "html_data", "search"],' . $tree . '});
                    $("#' . $node->id . '-tree-search").keyup(function() {
                        $("#' . $node->id . '-tree").jstree("search", $(this).val());
                    });
                    $("#' . $node->id . '-tree").on("select_node.jstree",
                       function (e, data) { document.location.href = data.node.original.href; })
                });
            </script>';
        return $html;
    }

    public function indexAction() {
        $nodes['system'] = [];
        $nodes['custom'] = [];
        foreach (Zend_Registry::get('nodes') as $node) {
            if ($node->extendable) {
                $nodeType = ($node->system) ? 'system' : 'custom';
                $nodes[$nodeType][$node->id] = ['node' => $node, 'tree' => self::treeSelect($node)];
            }
        }
        $this->view->nodes = $nodes;
    }

    public function insertAction() {
        $root = Model_NodeMapper::getById($this->_getParam('id'));
        if (!$root->extendable) {
            $this->_helper->message('error_forbidden');
            return $this->_helper->redirector->gotoUrl('/admin/hierarchy');
        }
        $form = new Admin_Form_Node();
        $form->addHierarchies('super', $root, true);
        if (!$root->directional) {
            $form->removeElement('inverse_text');
        }
        if ($this->_getParam('mode') == 'insert' || !$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $form->populate(['name' => trim($this->_getParam('name'))]);
            $this->view->form = $form;
            $this->view->node = $root;
            return;
        }
        $name = $form->getValue('name');
        $name .= ($form->getValue('inverse_text')) ? ' (' . $form->getValue('inverse_text') . ')' : '';
        $superIdValue = $form->getValue($root->nameClean . 'Id') ;
        $superId = ($superIdValue) ? $superIdValue : $root->id;
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $nodeId = Model_EntityMapper::insert($root->class->id, $name, $form->getValue('description'));
        Model_LinkMapper::insert($root->propertyToSuper, $nodeId, $superId);
        Zend_Db_Table::getDefaultAdapter()->commit();
        return $this->_helper->redirector->gotoUrl('/admin/hierarchy/#tab' . $root->id);
    }

    public function insertHierarchyAction() {
        $form = new Admin_Form_Hierarchy();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            return;
        }
        foreach (Zend_Registry::get('nodes') as $node) {
            if ($node->nameClean == \Craws\FilterInput::filter($form->getValue('name'), 'node')) {
                $this->view->form = $form;
                $this->_helper->message('error_name_exists');
                return;
            }
        }
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $hierarchyId = Model_EntityMapper::insert('E55', $form->getValue('name'), $form->getValue('description'));
        Model_NodeMapper::insertHierarchy($form, $hierarchyId, $form->getValue('name'));
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_insert');
        return $this->_helper->redirector->gotoUrl('/admin/hierarchy/#tab' . $hierarchyId);
    }

    public function updateAction() {
        $node = Model_NodeMapper::getById($this->_getParam('id'));
        if (!$node->superId || !$node->extendable) {
            $this->_helper->message('error_forbidden');
            return $this->_helper->redirector->gotoUrl('/admin/hierarchy');
        }
        $form = new Admin_Form_Node();
        $form->addHierarchies('super', $node, true);
        if (!$node->directional) {
            $form->removeElement('inverse_text');
        }
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $array = explode('(', $node->name);
            $inverse = (isset($array[1])) ? trim(str_replace(['(', ')'], '', $array[1])) : '';
            $form->populate([
                'description' => $node->description,
                'inverse_text' => $inverse,
                'name' => trim($array[0])
            ]);
            $this->view->root = ($node->rootId) ? Model_NodeMapper::getById($node->rootId) : NULL;
            $this->view->form = $form;
            $this->view->node = $node;
            return;
        }
        $inverse = trim(str_replace(['(', ')'], '', $form->getValue('inverse_text')));
        $node->name = str_replace(['(', ')'], '', $form->getValue('name'));
        $node->name .= ($inverse) ? ' (' . $inverse . ')' : '';
        $node->description = $this->_getParam('description');
        $root = Model_NodeMapper::getById($node->rootId);
        $superIdValue = $form->getValue($root->nameClean . 'Id') ;
        $superId = ($superIdValue) ? $superIdValue : $root->id;
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $node->update();
        $superLink = Model_LinkMapper::getLink($node, $node->propertyToSuper);
        $superLink->range = Model_NodeMapper::getById($superId);
        $superLink->update();
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/hierarchy/#tab' . $node->rootId);
    }

    public function updateHierarchyAction() {
        $hierarchy = Model_NodeMapper::getById($this->_getParam('id'));
        if ($hierarchy->system) {
            $this->_helper->message('error_forbidden');
            return $this->_helper->redirector->gotoUrl('/admin/hierarchy/#tab' . $hierarchy->id);
        }
        $form = new Admin_Form_Hierarchy();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            if ($hierarchy->multiple) {
                $form->getElement('multiple')->setAttribs(array('style' => 'display: none'));
                $form->getElement('multiple')->getDecorator('Label')->setOption('style', 'display: none');
            }
            $this->view->hierarchy = $hierarchy;
            $this->view->form = $form;
            $form->populate([
                'description' => $hierarchy->description,
                'name' => $hierarchy->name,
                'multiple' => $hierarchy->multiple
            ]);
            foreach ($hierarchy->forms as $hierarchyForm) {
                $form->forms->removeMultiOption($hierarchyForm['id']);
            }
            return;
        }
        if ($hierarchy->name != $form->getValue('name')) {
            foreach (Zend_Registry::get('nodes') as $node) {
                if ($node->nameClean == \Craws\FilterInput::filter($form->getValue('name'), 'node')) {
                    $this->view->form = $form;
                    $this->_helper->message('error_name_exists');
                    return;
                }
            }
        }
        $hierarchy->name = $form->getValue('name');
        $hierarchy->description = $form->getValue('description');
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $hierarchy->update();
        Model_NodeMapper::updateHierarchy($form, $hierarchy);
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/hierarchy/#tab' . $hierarchy->id);
    }

    public function viewAction() {
        $node = Model_NodeMapper::getById($this->_getParam('id'));
        $linksEntitites = $node->getLinkedEntities($node->propertyToEntity, true);
        if ($node->class->code == 'E53') {
            $linksEntitites = [];
            foreach ($node->getLinkedEntities($node->propertyToEntity, true) as $object) {
                $linkedEntity = $object->getLinkedEntity('P53', true);
                if ($linkedEntity) { // needed to remove node subs
                    $linksEntitites[] = $linkedEntity;
                }
            }
        }
        $this->view->node = $node;
        $this->view->root = ($node->rootId) ? Model_NodeMapper::getById($node->rootId) : NULL;
        $this->view->linksEntities = $linksEntitites;
        $this->view->linksProperties = Model_LinkPropertyMapper::getByEntity($node);
    }

}
