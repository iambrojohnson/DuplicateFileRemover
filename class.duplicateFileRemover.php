<?php

/**
 * @package DuplicateFileRemover
                               * @author Johnson Omotosho
                               * @link http://github.com/brojohnson
                               * @version 0.0.1
                               */

namespace Amantosh;

use Exception;

class DuplicateFileRemover
{

    protected $dir, $files;
    public $showProgress = true;

    /**
     *  Constructor.
     * 
     *  Set path to the directory to remove deuplicate found.
     * 
     *  @param string $path Path to the valid diretory. 
     */

    public function __construct(string $path)
    {

        if (!is_dir($path)) {
            throw new Exception("$path is not a valid directory.");
        }
        $this->dir = $path;
        $the_files = scandir($this->dir);
        $this->files = array_filter($the_files, function ($item) {
            return !($item == "." or $item == ".."
                /**
                     *  Ignore sub-directories.
                     *  call setPath method to scan new directories.
                     */
                or is_dir($item)
                /**
                  * Uncomment this to specify some files to  exclude after files had been    scanned.
                 */
                // or ! in_array('some files to exclude!')
            );
        });
    }

    /**
     *  Function to call to start removing duplicates.
     * 
     *  @return self
     */

    public function start_process(): self
    {
        array_walk($this->files, array($this, 'remove_duplicates'));
        return $this;
    }

    /**
     *  Set new path for the class to scan.
     * 
     *  @param string $path The new path to set for the class.
     *  
     *  @return  self
     */

    public function setPath(string $path): self
    {
        $this->__construct($path);
        return $this;
    }

    private function showProgress(string $msg): void
    {
        if (!$this->showProgress) return;
        echo $msg;
    }


    /**
     *  Concatanates files with its directory.
     * 
     *   @param string $file The filename
     * 
     *   @return string
     */
    private function join_dir(string $file): string
    {
        return $this->dir . DIRECTORY_SEPARATOR . $file;
    }

    /**
     *  Deletes files that are found to be duplicates.
     * 
     *  This function deletes arrays of files that are found to be duplicates. 
     *  
     * 
     *  @param array  $file Array of files given to delete
     * 
     *  @return viod
     */
    private function delete_all_files(array $file): void
    {

        $file_to_delete =  array_map(function ($filename) use ($file) {
            if ($filename == $file) return null;
            return $this->join_dir($filename);
        }, $file);
        array_walk($file_to_delete, function ($item) {

            if (file_exists($item)) {
                $this->showProgress("\tDeleting $item\n");
                unlink($item);
            }
        });
    }

    /**
     *  Seperate  duplcates files.
     * 
     *  This function removes duplicates files by comparing their sha1.
     *  
     *  @param $item File item to compare.
     * 
     *  @return viod
     */

    private function remove_duplicates($item): void
    {

        $this->showProgress("\nReading $item\n");
        $is_duplicate_file = array_filter($this->files, function ($cur_file) use ($item) {

            $this->showProgress("\tComparing $cur_file\n");

            $cur_file = $this->join_dir($cur_file);
            $item = $this->join_dir($item);

            if (!file_exists($cur_file) or !file_exists($item)) return null;
            /**
             * This process is very slow
             * I wished I'd a better way to do this.
             */
            return sha1_file($cur_file) == sha1_file($item);
        });

        /**
         *  Check if we have over 1 duplicates files
         *  if true then remove one of the files and delete the rest.
         */

        if (count($is_duplicate_file) > 1) {
            array_shift($is_duplicate_file);
            $this->delete_all_files($is_duplicate_file);
        }
    }
}
