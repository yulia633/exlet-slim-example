<?php

namespace App;

class Repository
{
    private $data = [];
    private $stream;
    private const PATH = __DIR__ . '/../repo/users';

    public function add(array $data)
    {
        $id = uniqid();
        $data['id'] = $id;
        $formattedData = $this->writeFile($data);
    }

    public function all()
    {
        $filename = pathinfo(self::PATH, PATHINFO_FILENAME);
        $jsonData = $this->readFile($filename);
        return $jsonData;
    }

    public function get($id)
    {
        $allData = $this->all();
        $data = collect($allData)->firstWhere('id', $id);
        return $data;
    }

    public function save($data)
    {
        $id = $data->id;
        $allData = $this->all();
        $currentData = $this->get($id);
        $indexOfData = array_search($currentData, $allData);
        $allData[$indexOfData] = $data;
        $this->writeFile($data);
    }

    public function readFile(string $filename): array
    {
        if (!file_exists($filename)) {
            touch($filename);
        }

        $this->stream = fopen($filename, "r+");

        if (!flock($this->stream, LOCK_EX | LOCK_NB)) {
            throw new \Exception("The file {$filename} unable to lock.");
        }

        $content = json_decode(stream_get_contents($this->stream), true);

        if ($content === null) {
            $content = [];
        }
        
        return $content;
    }

    public function writeFile(array $data)
    {
        ftruncate($this->stream, 0);
        fseek($this->stream, 0);
        fwrite($this->stream, json_encode($data));

        flock($this->stream, LOCK_UN);
        fclose($this->stream);
    }
}
