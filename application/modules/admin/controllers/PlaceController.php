<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_PlaceController extends Zend_Controller_Action {

    public function deleteAction() {
        Model_EntityMapper::getById($this->_getParam('id'))->delete();
        $this->_helper->message('info_delete');
        return $this->_helper->redirector->gotoUrl('/admin/place');
    }

    public function indexAction() {
        $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
        $this->view->jsonData = Model_GisMapper::getAll();
    }

    public function insertAction() {
        $source = null;
        if ($this->_getParam('sourceId')) {
            $source = Model_EntityMapper::getById($this->_getParam('sourceId'));
            $this->view->menuHighlight = 'source';
        }
        $form = new Admin_Form_Place();
        $hierarchies = $form->addHierarchies('Place');
        $form->addElement($form->createElement('text', 'alias0', ['belongsTo' => 'alias']));
        if ($this->getRequest()->isPost()) {
            $form->preValidation($this->getRequest()->getPost());
        }
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            $this->view->source = $source;
            return;
        }
        $object = Model_EntityMapper::insert('E18', $form->getValue('name'), $form->getValue('description'));
        $place = Model_EntityMapper::insert('E53', 'Location of ' . $form->getValue('name'));
        Model_LinkMapper::insert('P53', $object, $place);
        self::save($form, $object, $place, $hierarchies);
        if ($source) {
            Model_LinkMapper::insert('P67', $source, $object);
        }
        $this->_helper->message('info_insert');
        $url = '/admin/place/view/id/' . $object->id;
        if ($form->getElement('continue')->getValue() && $source) {
            $url = '/admin/place/insert/sourceId/' . $source->id;
        } else if ($form->getElement('continue')->getValue()) {
            $url = '/admin/place/insert';
        } else if ($source) {
            $url = '/admin/source/view/id/' . $source->id . '/#tabPlace';
        }
        return $this->_helper->redirector->gotoUrl($url);
    }

    public function updateAction() {
        $object = Model_EntityMapper::getById($this->_getParam('id'));
        $place = Model_LinkMapper::getLinkedEntity($object, 'P53');
        $form = new Admin_Form_Place();
        $hierarchies = $form->addHierarchies('Place', $object);
        $this->view->form = $form;
        $this->view->object = $object;
        $form->prepareUpdate($object);
        if (!$this->getRequest()->isPost()) {
            self::prepareDefaultUpdate($form, $object, $place);
            return;
        }
        $form->preValidation($this->getRequest()->getPost());
        $formValid = $form->isValid($this->getRequest()->getPost());
        $modified = Model_EntityMapper::checkIfModified($object, $form->modified->getValue());

        if ($modified) {
            $log = Model_UserLogMapper::getLogForView('entity', $object->id);
            $this->view->modifier = $log['modifier_name'];
        }
        if (!$formValid || $modified) {
            $this->_helper->message('error_modified');
            return;
        }
        $object->name = $form->getValue('name');
        $object->description = $form->getValue('description');
        $object->update();
        foreach (Model_LinkMapper::getLinks($object, 'P2') as $objectLink) {
            $objectLink->delete();
        }
        $place->name = 'Location of ' . $form->getValue('name');
        $place->update();
        Model_GisMapper::deleteByEntity($place);
        foreach (Model_LinkMapper::getLinkedEntities($object, 'P1') as $alias) {
            $alias->delete();
        }
        foreach (Model_LinkMapper::getLinks($place, 'P89') as $link) {
            $link->delete();
        }
        self::save($form, $object, $place, $hierarchies);
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/place/view/id/' . $object->id);
    }

    public function viewAction() {
        $object = Model_EntityMapper::getById($this->_getParam('id'));
        $place = Model_LinkMapper::getLinkedEntity($object, 'P53');
        $this->view->gis = Model_GisMapper::getByEntity($place);
        if ($this->view->gis) {
            $this->view->jsonData = Model_GisMapper::getJsonData();
        }
        $this->view->object = $object;
        $this->view->aliases = Model_LinkMapper::getLinkedEntities($object, 'P1');
        $this->view->dates = Model_DateMapper::getDates($object);
        $this->view->administrative = Model_NodeMapper::getNodesByEntity('Administrative Unit', $object);
        $this->view->historicals = Model_NodeMapper::getNodesByEntity('Historical Place', $object);
        $this->view->events = array_merge(
            Model_LinkMapper::getLinkedEntities($place, 'P7', true), Model_LinkMapper::getLinkedEntities($object, 'P24', true)
        );
        $this->view->actorLinks = array_merge(
            Model_LinkMapper::getLinks($place, 'P74', true),
            Model_LinkMapper::getLinks($place, 'OA8', true),
            Model_LinkMapper::getLinks($place, 'OA9', true)
        );
        $sourceLinks = [];
        $referenceLinks = [];
        foreach (Model_LinkMapper::getLinks($object, 'P67', true) as $link) {
            switch ($link->domain->class->code) {
                case 'E31':
                    $referenceLinks[] = $link;
                    break;
                case 'E33':
                    $sourceLinks[] = $link;
                    break;
            }
        }
        $this->view->sourceLinks = $sourceLinks;
        $this->view->referenceLinks = $referenceLinks;
    }

    private function prepareDefaultUpdate(Zend_Form $form, Model_Entity $object, Model_Entity $place) {
        $points = Model_GisMapper::getPoints($place);
        $polygons = Model_GisMapper::getPolygons($place);
        $this->view->points = json_encode($points);
        $this->view->polygons = $polygons;
        $this->view->points2 = json_encode(Model_GisMapper::getPoints2($place));
        $this->view->points2 = Model_GisMapper::getPoints2($place);
        $form->populate([
            'name' => $object->name,
            'description' => $object->description,
            'modified' => ($object->modified) ? $object->modified->getTimestamp() : 0,
            'gisPoints' => json_encode($points)
        ]);
        $form->populateDates($object, ['OA1' => 'begin', 'OA2' => 'end']);
        return;
    }

    private function save(Zend_Form $form, Model_Entity $object, Model_Entity $place, array $hierarchies) {
        foreach ($hierarchies as $hierarchy) {
            $idField = $hierarchy->nameClean . 'Id';
            if ($form->getValue($idField)) {
                if ($hierarchy->propertyToEntity == 'P89') {
                    foreach (explode(",", $form->getValue($idField)) as $id) {
                        Model_LinkMapper::insert($hierarchy->propertyToEntity, $place, $id);
                    }
                } else {
                    foreach (explode(",", $form->getValue($idField)) as $id) {
                        Model_LinkMapper::insert($hierarchy->propertyToEntity, $object, $id);
                    }
                }
            } else if ($hierarchy->system && $hierarchy->propertyToEntity != 'P89') {
                Model_LinkMapper::insert($hierarchy->propertyToEntity, $object, $hierarchy);
            }
        }
        Model_DateMapper::saveDates($object, $form);
        $data = $form->getValues();
        foreach (array_unique($data['alias']) as $name) {
            if (trim($name)) {
                $alias = Model_EntityMapper::insert('E41', trim($name));
                Model_LinkMapper::insert('P1', $object, $alias);
            }
        }
        Model_GisMapper::insertPoints($place, json_decode($form->gisPoints->getValue()));
        if ($form->getValue('gisData')) {
            $gisData = $form->getValue('gisData');
            parse_str($gisData, $output);
            $geom = "(SELECT ST_GeomFromText('" . $output['geometrytype'] . "'(" . $output['shapecoords'] . ")', 4326))";
            $geom = "SELECT ST_GeomFromText('polygon(( -112.0781421661377 68.5539591738857, -98.0156421661377 52.738954993199584, -67.0781421661377 68.80957565002588, -112.0781421661377 68.5539591738857))', 4326)";
            switch($output['geometrytype']) {
              case 'polygon':
                  var_dump('inside');
                  $sql = "
                    INSERT INTO gis.polygon (entity_id, name, description, type, geom)
                    VALUES (:entity_id, :name, :description, :type, ST_GeomFromText('polygon(".
                    $output['shapecoords'] ."
                    )', 4326));";
                  $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
                  $statement->bindValue(':entity_id', $place->id);
                  $statement->bindValue(':name', $output['shapename']);
                  $statement->bindValue(':description', $output['shapedescription']);
                  $statement->bindValue(':type', $output['shapetype']);
                  $statement->execute();
                  $result = $statement->fetch(PDO::FETCH_ASSOC);
                  break;
          }
        }

      }
}
