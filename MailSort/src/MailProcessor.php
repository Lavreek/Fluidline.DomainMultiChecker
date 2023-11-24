<?php
namespace App\MailSort;

class MailProcessor
{
    private array $unifyDomens = [];
    private array $unifyMails = [];

    public function processFile($url)
    {
        $content = file_get_contents($url);
        // $content = mb_convert_encoding($content, 'UTF-8', $sourceEncoding);

        $words = preg_split("/\r\n|\r/", $content);
        foreach ($words as $word) {
            
            $domen = $this->processWord($word, $words);
            if (!in_array($domen, $this->unifyDomens)) {
                $this->unifyDomens[] = $domen;
                $this->unifyMails[] = $word;

            }
        }
        $this->unifyMailList($this->unifyMails);
    }

    private function processWord(string $word, array $words): string
    {
        $fn = function(string $w) {
            $wl = preg_split('/[@]/', $w);
            return $wl[1];
        };

        if (!empty($word)) {
            $wordLetters = preg_split('/[@]/', $word);
            $shortedArray = array_map($fn, $words);
            $countedShortedArray = array_count_values($shortedArray);
            if ($countedShortedArray[$wordLetters[1]] > 1) {
                $folderName = implode("_", preg_split('/\./', $wordLetters[1]));
                $folderPath = "library";
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0777, true);
                }
                $filePath = fopen("library/{$folderName}.csv", "a+");
                
                fputcsv($filePath, [iconv("UTF-8", "Windows-1251", $word)]);
                fclose($filePath);
            }
            
            

        }

        return $wordLetters[1];
    }

    public function handleWebUpload($file)
    {
        if (! empty($file)) {
            $content = file_get_contents($file);
            $words = preg_split("/\r\n|\r/", $content);

            foreach ($words as $word) {
                $domen = $this->processWord($word, $words);
                if (!in_array($domen, $this->unifyDomens)) {
                    $this->unifyDomens[] = $domen;
                    $this->unifyMails[] = $word;
                }
            }
            $this->unifyMailList($this->unifyMails);
            return $this->unifyDomens;
        }
    }

    private function unifyMailList(array $list): void
    {
        foreach ($list as $item) {
            $folderPath = "library";
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }
            $filePath = fopen("library/!unify.csv", "a+");
            fputcsv($filePath, [iconv("UTF-8", "Windows-1251", $item)]);
            fclose($filePath);
        }
    }
}
