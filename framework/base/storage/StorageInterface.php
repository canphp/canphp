<?php
namespace framework\base\storage;

Interface StorageInterface {

    public function read($name);
	
    public function write($name, $content, $option);
	
	public function append($name, $content);
	
	public function delete($name);

	public function isExists($name);	
	
	public function move($oldName, $newName);	
}