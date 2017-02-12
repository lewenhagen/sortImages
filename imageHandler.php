<?php

class ImageHandler {
    private $baseFolder = "";
    private $currentFileName = "";
    private $currentFile = "";
    private $unfinishedFolder = "";
    private $finishedFolder = "";

    private $fullYear = ["Januari", "Februari", "Mars", "April", "Maj", "Juni", "Juli", "Augusti", "September", "Oktober", "November", "December"];

    private $filesUnfinished = 0;
    private $filesTotal = 0;
    private $photos = 0;
    private $videos = 0;
    private $matches = 0;
    private $errors = array();
    private $matchedOriginal = array();
    private $matchedCopy = array();



    public function __construct($baseFolder, $unfinishedFolder, $finishedFolder) {
        $this->baseFolder = $baseFolder;
        $this->unfinishedFolder = $unfinishedFolder;
        $this->finishedFolder = $finishedFolder;
    }



    public function is_image ($path) {
        $result = false;
        $a = @getimagesize($path);
        $image_type = $a[2];

        if(in_array($image_type , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP)))
        {
            $result = true;
        }
        return $result;
    }



    public function getBaseFolder() {
        return $this->baseFolder;
    }



    public function fixFoldersAndMakeCopy ($y, $m) {

        // Set finished folder name
        $finishedFolder = __DIR__ . "/" . $this->finishedFolder;

        // Create folder if not exists
        if (!file_exists($finishedFolder)) {
            mkdir($finishedFolder);
        }

        $currentYearFolder = __DIR__ . "/" . $this->finishedFolder . "/" . $y;

        // Create folder if not exists
        if (!file_exists($currentYearFolder)) {
            mkdir($currentYearFolder);
        }

        $currentMonthFolder = $currentYearFolder . "/" . $this->fullYear[$m-1];

        // Create folder if not exists
        if (!file_exists($currentMonthFolder)) {
            mkdir($currentMonthFolder);
        }

        // Make the copy
        if (file_exists($currentMonthFolder . "/" . $this->currentFile->getFileName())) {

            if (!file_exists("original")) {
                mkdir($finishedFolder . "/" . "original/");
            }
            if (!file_exists("copy")) {
                mkdir($finishedFolder . "/" . "copy/");
            }
            copy($currentMonthFolder . "/" . $this->currentFile->getFileName(), $finishedFolder . "/" . "original/" . $this->currentFile->getFileName());
            copy($this->currentFileName, $finishedFolder . "/" . "copy/" . $this->currentFile->getFileName());


            // if (!file_exists($finishedFolder . "/" . "dubbles")) {
            //     mkdir($finishedFolder . "/" . "dubbles");
            // }
            // copy($this->currentFileName, $finishedFolder . "/" . "dubbles/" . $this->currentFile->getFileName());
            // array_push($this->matchedCopy, $this->currentFileName);
            // array_push($this->matchedOriginal, $currentMonthFolder . "/" . $this->currentFile->getFileName());
            $this->matches++;
        } else {
            copy($this->currentFileName, $currentMonthFolder . "/" . $this->currentFile->getFileName());
        }

    }

    public function fixFile($currFile, $currFileName) {

        $this->currentFileName = $currFileName;
        $this->currentFile = $currFile;

        if ($this->is_image($this->currentFile)) {

            $exif_data = exif_read_data ($this->currentFile);

            if (!empty($exif_data['DateTimeOriginal'])) {

                $exif_date = $exif_data['DateTimeOriginal'];

                $info = explode(":", $exif_date);

                // Set the year variable if there is any
                $year = (string)$info[0];

                // Set the month variable if there is any
                $month = (string)$info[1];

                $this->fixFoldersAndMakeCopy($year, $month);

                // Increase nr of photos
                $this->photos++;

            } else {
                if(!file_exists(__DIR__ . "/" . $this->unfinishedFolder)) {
                    mkdir(__DIR__ . "/" . $this->unfinishedFolder);
                }

                // Unable to fix, copy to unfinished folder
                copy($this->currentFileName, __DIR__ . "/" . $this->unfinishedFolder . "/" . $this->currentFile->getFileName());

                $this->filesUnfinished++;

                array_push($this->errors, $this->currentFileName);
            }

            $this->filesTotal++;

        // Check if video
    } else if (preg_match('/^.*\.(mp4|mov|mpg|mpeg|wmv|mkv|3gp|avi)$/i', $this->currentFileName)) {

            $currentVideoFolder = $this->finishedFolder . "/" . "Video";

            if (!file_exists($currentVideoFolder)) {
                mkdir($currentVideoFolder);
            }

            copy($this->currentFileName, $currentVideoFolder . "/" . $this->currentFile->getFileName());
            $this->filesTotal++;
            $this->videos++;
        }
    }

    public function initFiles() {
        $dir = new RecursiveDirectoryIterator($this->baseFolder);

        foreach (new RecursiveIteratorIterator($dir) as $filename => $file) {

            $this->fixFile($file, $filename);
        }
    }

    public function getResult () {
        $res = "<table>";

        $res .= "<tr><td class='infoTd'>";
        $res .= "Files Total";
        $res .= "</td><td>";
        $res .= $this->filesTotal;
        $res .= "</td></tr>";

        $res .= "<tr><td class='infoTd'>";
        $res .= "Photos Success";
        $res .= "</td><td>";
        $res .= $this->photos;
        $res .= "</td></tr>";

        $res .= "<tr><td class='infoTd'>";
        $res .= "Videos Success";
        $res .= "</td><td>";
        $res .= $this->videos;
        $res .= "</td></tr>";

        $res .= "<tr><td class='infoTd'>";
        $res .= "Fail";
        $res .= "</td><td>";
        $res .= $this->filesUnfinished;
        $res .= "</td></tr>";

        $res .= "<tr><td class='infoTd'>";
        $res .= "Matches";
        $res .= "</td><td>";
        $res .= $this->matches;
        $res .= "</td></tr>";

        // $res .= "<tr><td class='infoTd'>";
        // $res .= "Matched original: ";
        // $res .= "</td><td>";
        // foreach ($this->matchedOriginal as $key) {
        //     $res .= $key . "<br>";
        // }
        // $res .= "</td></tr>";
        //
        // $res .= "<tr><td class='infoTd'>";
        // $res .= "Matched copy: ";
        // $res .= "</td><td>";
        // foreach ($this->matchedCopy as $key) {
        //     $res .= $key . "<br>";
        // }
        // $res .= "</td></tr>";

        // $res .= "<tr><td class='infoTd'>";
        // $res .= "Failed files: ";
        // $res .= "</td><td>";
        // foreach ($this->errors as $key) {
        //     $res .= $key . "<br>";
        // }
        // $res .= "</td></tr>";
        $res .= "</table>";

        return $res;
    }

}

?>
