<?php
 
class ItemsController extends Controller {

    function beforeAction () {

    }  
 
    function view($id = null,$name = null) {
     
        $this->set('title',$name.' - My Todo List App');
        $this->set('todo',$this->Item->select($id));
        $this->set('sql',$this->Item->getResult());
 
    }
     
    function viewall() {
 
        $this->set('title','All Items - My Todo List App');
        $this->set('todo',$this->Item->selectAll());
    }
     
    function add() {
        $todo = $_POST['todo'];
        $this->set('title','Success - My Todo List App');
        $this->set('todo',$this->Item->sql('insert into items (item_name) values (\''.mysql_real_escape_string($todo).'\')'),"insert");  
    }
     
    function delete($id = null) {
        $this->set('title','Success - My Todo List App');
        $this->set('todo',$this->Item->sql('delete from items where id = \''.mysql_real_escape_string($id).'\''),"delete");  
        $this->set('sql',$this->Item->getSql());
    }

    function afterAction() {

    }
 
}