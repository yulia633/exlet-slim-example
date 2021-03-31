<?php

namespace App;

class Repository
{
    private const PATH = __DIR__ . '/../repo/users';

    public function readFile(string $filePath): string
    {
        if (!file_exists(self::PATH)) {
            throw new \Exception("The file file path does not exists.");
        }
        
        return (string) file_get_contents(self::PATH);
    }


    public function encodeData($data)
    {
        $formattedData = "\n" . json_encode($data);
        file_put_contents(self::PATH, $formattedData, FILE_APPEND);   
    }

    public function add(array $data)
    {
        $id = uniqid();
        $data['id'] = $id;
        $this->encodeData($data);
    }
    
    public function all()
    {
        $json = $this->readFile(self::PATH);
        $dataStrings = explode("\n", $json);
        $data = array_map(fn ($string) => json_decode($string), $dataStrings);
        return $data;
    }

    public function get($id)
    {
        $allData = $this->all();
        $data = collect($allData)->firstWhere('id', $id);
        return $data;
    }

    public function destroy(string $id)
    {
        unset($id);
    }

    public function save($data)
    {
        $id = $data->id;
        $allData = $this->all();
        $currentData = $this->get($id);
        $indexOfData = array_search($currentData, $allData);
        $allData[$indexOfData] = $data;
        $out = array_map(fn ($item) => $this->encodeData($item), $allData);
    }
}
