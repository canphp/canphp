<?php
namespace framework\ext\upload;

Interface UploadInterface {

	public function __construct($config);

	public function rootPath($path);

    public function checkPath($path);

    public function saveFile($file);

    public function getError();
		
}