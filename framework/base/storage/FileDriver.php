<?php
namespace framework\base\storage;
class FileDriver implements StorageInterface{

    public function read($name){
		return file_get_contents($name);
	}
	
    public function write($name, $content, $option=null){
		return file_put_contents($name, $content, LOCK_EX);
	}
	
	public function append($name, $content){
		return file_put_contents($name, $content, LOCK_EX|FILE_APPEND);
	}
	
	public function delete($name){
		return @unlink($name);
	}

	public function isExists($name){
		return file_exists($name);
	}	
	
	public function move($oldName, $newName){
		return rename($oldName, $newName);
	} 
}