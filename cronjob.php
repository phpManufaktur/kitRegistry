<?php

// first we need the LEPTON config.php
require_once('../../config.php');

require_once WB_PATH.'/modules/kit_registry/initialize.php';
require_once WB_PATH.'/framework/functions.php';

class checkRegistryUpload {

  private $upload_directory = '/media/upload';
  private $registry_group = 'werkszeugnisse';

  /**
   * Iterate directory tree very efficient
   * Function postet from donovan.pp@gmail.com at
   * http://www.php.net/manual/de/function.scandir.php
   *
   * @param string $directory
   * @return array - directoryTree
   */
  public static function getDirectoryTree($directory, $extensions_only = NULL) {
    if (substr($directory, -1) == "/") $directory = substr($directory, 0, -1);
    $path = array();
    $stack = array();
    $stack[] = $directory;
    while ($stack) {
      $thisdir = array_pop($stack);
      if (false !== ($dircont = scandir($thisdir))) {
        $i = 0;
        while (isset($dircont[$i])) {
          if ($dircont[$i] !== '.' && $dircont[$i] !== '..') {
            $current_file = "{$thisdir}/{$dircont[$i]}";
            if (is_file($current_file)) {
              if ($extensions_only == NULL) {
                $path[] = "{$thisdir}/{$dircont[$i]}";
              }
              else {
                $path_info = pathinfo("{$thisdir}/{$dircont[$i]}");
                if (isset($path_info['extension']) && in_array($path_info['extension'], $extensions_only)) $path[] = "{$thisdir}/{$dircont[$i]}";
              }
            }
            elseif (is_dir($current_file)) {
              $stack[] = $current_file;
            }
          }
          $i++;
        }
      }
    }
    return $path;
  } // getDirectoryTree()

  protected function checkDirectory() {
    global $dbKITregistryFiles;
    $path = WB_PATH.$this->upload_directory;
    if (!file_exists($path)) {
      @mkdir($path, 0755);
    }
    $files = $this->getDirectoryTree($path);
    foreach ($files as $file) {
      $path_info = pathinfo($file);
      if (isset($path_info['extension']) && (strtolower($path_info['extension']) == 'pdf')) {
        // check if this file exists in registry
        $SQL = sprintf("SELECT %s FROM %s WHERE `%s`='%s'",
            dbKITregistryFiles::field_id,
            $dbKITregistryFiles->getTableName(),
            dbKITregistryFiles::field_filename_original,
            basename($file));
        $result = array();
        if (!$dbKITregistryFiles->sqlExec($SQL, $result)) {
          exit(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbKITregistryFiles->getError()));
        }
        if (count($result) > 0) {
          // this file already exists, delete it and go to the next
          @unlink($file);
          continue;
        }

        // add file to the registry
        $registry_filename = page_filename(basename($file));
        $sub_directory = $registry_filename[0];
        $registry_path = WB_PATH.'/media/kit_protected/registry/'.$sub_directory;
        if (!file_exists($registry_path)) {
          @mkdir($registry_path);
        }
        $registry_path .= '/'.$registry_filename;

        if (!rename($file, $registry_path)) {
          exit(sprintf('Can not copy the file %s to the kitRegsitry folder!', basename($file)));
        }
        $data = array(
            dbKITregistryFiles::field_filename_original => basename($file),
            dbKITregistryFiles::field_filename_registry => basename($registry_filename),
            dbKITregistryFiles::field_filepath_registry => $registry_path,
            dbKITregistryFiles::field_filetype => strtolower($path_info['extension']),
            dbKITregistryFiles::field_filesize => filesize($file),
            dbKITregistryFiles::field_filemtime => filemtime($file),
            dbKITregistryFiles::field_sub_dir => $sub_directory,
            dbKITregistryFiles::field_content_groups => $this->registry_group,
            dbKITregistryFiles::field_protect => dbKITregistryFiles::protect_none,
            dbKITregistryFiles::field_status => dbKITregistryFiles::status_active
            );
        if (!$dbKITregistryFiles->sqlInsertRecord($data)) {
          exit(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbKITregistryFiles->getError()));
        }

      }
    }
  } // checkDirectory()

  public function action() {
    $this->checkDirectory();
  } // action()

} // class checkRegistryUpload

$check = new checkRegistryUpload();
$check->action();