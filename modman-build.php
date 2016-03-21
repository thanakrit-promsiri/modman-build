<?php

class Modman_build {

    /**
     * the .modman directory is missing
     */
    const MODMANPATH = '../';
    const ROOTMODMANPATH = '../../';
    const BUILDDIRNAME = '.modman-build';

    private $build_path;
    private $ignorefile = array(
        ".",
        "..",
        ".git",
        "modman",
        "README.md"
    );

    public function Modman_build() {
        $this->build_path = Modman_build::ROOTMODMANPATH . Modman_build::BUILDDIRNAME;
    }

    public function run() {
        $currentDir = basename(__DIR__);
        $modmanDir = scandir(Modman_build::MODMANPATH);
        $modmanDir = $this->removeModmanDirVarUnused($modmanDir, $currentDir);
        $this->cleanBuildDir();
        print_r($modmanDir);
        foreach ($modmanDir as $moduleName) {
            $moduleFileList = $this->removeModuleDirVarUnused($moduleName);
            $this->copyTobuild($moduleName, $moduleFileList);
        }
    }

    private function copyTobuild($moduleName, $moduleFileList) {
        foreach ($moduleFileList as $moduleFile) {

            $source = Modman_build::MODMANPATH . $moduleName . '/' . $moduleFile;
            $dest = $this->build_path. '/' . $moduleFile;
            try {
                $this->copy_directory($source, $dest);
                echo "Copy Successful....";
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }         
        }
    }

    private function cleanBuildDir() {
        $build_path = $this->build_path;
        if (file_exists($build_path)) {
            $this->deleteDir($build_path);
            mkdir($build_path);
        } else {
            mkdir($build_path);
        }
    }

    private function removeModmanDirVarUnused($modmanDir, $currentDir) {
        $modmanDir = $this->removeValue($modmanDir, $currentDir);
        $modmanDir = $this->ignorePath($modmanDir);
        return $modmanDir;
    }

    private function removeModuleDirVarUnused($file) {
        $filePath = Modman_build::MODMANPATH . $file;
        $fileArr = scandir($filePath);
        return $this->ignorePath($fileArr);
    }

    private function ignorePath($modmanDir) {
        foreach ($this->ignorefile as $value) {
            $modmanDir = $this->removeValue($modmanDir, $value);
        }
        return $modmanDir;
    }

    private function removeValue($dirArr, $removeValue) {
        if (($key = array_search($removeValue, $dirArr)) !== false) {
            unset($dirArr[$key]);
        }
        return $dirArr;
    }

    private function deleteDir($dir) {
        if (substr($dir, strlen($dir) - 1, 1) != '/')
            $dir .= '/';

        echo $dir;

        if ($handle = opendir($dir)) {
            while ($obj = readdir($handle)) {
                if ($obj != '.' && $obj != '..') {
                    if (is_dir($dir . $obj)) {
                        if (!$this->deleteDir($dir . $obj))
                            return false;
                    }
                    elseif (is_file($dir . $obj)) {
                        if (!unlink($dir . $obj))
                            return false;
                    }
                }
            }

            closedir($handle);

            if (!@rmdir($dir))
                return false;
            return true;
        }
        return false;
    }

    private function copy_directory($source, $destination) {
        if (is_dir($source)) {
            @mkdir($destination);
            $directory = dir($source);
            while (FALSE !== ( $readdirectory = $directory->read() )) {
                if ($readdirectory == '.' || $readdirectory == '..') {
                    continue;
                }
                $PathDir = $source . '/' . $readdirectory;
                if (is_dir($PathDir)) {
                    $this->copy_directory($PathDir, $destination . '/' . $readdirectory);
                    continue;
                }
                copy($PathDir, $destination . '/' . $readdirectory);
            }

            $directory->close();
        } else {
            copy($source, $destination);
        }
    }

}

$modman_build = new Modman_build();
$modman_build->run();
