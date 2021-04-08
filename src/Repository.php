<?php

namespace App;

class Repository
{
    private $path;

    public $data;

    public function __construct($path)
    {
        $this->path = $path . '.json';
        if (!file_exists($this->path)) {
            file_put_contents($this->path, '[]');
            $this->data = [];
        } else {
            $this->readFile();
        }
    }

    private function readFile()
    {
        $this->data = json_decode(file_get_contents($this->path), true);
    }

    public function save()
    {
        file_put_contents($this->path, json_encode($this->data));
    }

    public function add(array $row = [])
    {
        if (!array_key_exists('id', $row)) {
            $row['id'] = uniqid();
            while (count($this->select(['id' => $row['id']])) > 0) {
                $row['id'] = uniqid();
            }
        } 

        $this->data[] = $row;
        $this->save();
        return $row;
    }

    public function add1(array $row = [])
    {
       $oldData = $this->select(['id' => $row['id']]);
    //    var_dump($oldData);

    //    var_dump($row); //die;

       $result = [];
       foreach ($oldData as $key => $value) {
          foreach ($value as $item) {
        $oldData[$key] = $row;
        // die;
          }

          //var_dump($oldData);
     }

    // var_dump($oldData);die;

     


      // $newData[] = $row;
    //   var_dump($this->data);
    //     var_dump($row);
    //     $this->data[] = $row;
    //     var_dump($this->data);
    //     die;

      // var_dump($newData);

     $this->data[] = $oldData;
     var_dump($this->data);die;
      $this->save();
        return $oldData;
    }

    public function select(array $whereEquals = [])
    {
        $results = [];
        $this->isCheckRow(
            $whereEquals,
            function ($row, $index) use (&$results) {
                $results[] = $row;
            }
        );
        return $results;
    }


    public function destroy(array $whereEquals = [])
    {
        $toDelete = [];
        $this->isCheckRow(
            $whereEquals,
            function ($row, $index) use (&$toDelete) {
                $toDelete[] = $index;
            }
        );
        //loop backwords to avoid indeces going out of sync.
        for ($i = count($toDelete) - 1; $i >= 0; $i -= 1) {
            unset($this->data[$toDelete[$i]]);
        }
        $this->data = $this->reIndex($this->data);
        $this->save();
    }

    private function isCheckRow(array $whereEquals, $callback)
    {
        for ($i = 0; $i < count($this->data); $i += 1) {
            $row = $this->data[$i];
            $isMatch = true;
            foreach ($whereEquals as $column => $value) {
                if (!array_key_exists($column, $row) || $row[$column] != $value) {
                    $isMatch = false;
                }
            }
            if ($isMatch) {
                $callback($row, $i);
            }
        }
    }

    // Переиндексировать массив
    private function reIndex(array $array)
    {
        $reIndex = [];
        foreach ($array as $value) {
            $reIndex[] = $value;
        }
        return $reIndex;
    }

    // private const PATH = __DIR__ . '/../repo/users';

    // public function readFile(string $filePath): string
    // {
    //     if (!file_exists(self::PATH)) {
    //         throw new \Exception("The file file path does not exists.");
    //     }
        
    //     return (string) file_get_contents(self::PATH);
    // }


    // public function encodeData($data)
    // {
    //     $formattedData = "\n" . json_encode($data);
    //     file_put_contents(self::PATH, $formattedData, FILE_APPEND);   
    // }

    // public function add(array $data)
    // {
    //     $id = uniqid();
    //     $data['id'] = $id;
    //     $this->encodeData($data);
    // }
    
    // public function all()
    // {
    //     $json = $this->readFile(self::PATH);
    //     $dataStrings = explode("\n", $json);
    //     $data = array_map(fn ($string) => json_decode($string), $dataStrings);
    //     return $data;
    // }

    // public function get($id)
    // {
    //     $allData = $this->all();
    //     $data = collect($allData)->firstWhere('id', $id);
    //     return $data;
    // }

    // public function destroy(string $id)
    // {
    //     unset($id);
    // }

    // public function save($data)
    // {
    //     $id = $data->id;
    //     $allData = $this->all();
    //     $currentData = $this->get($id);
    //     $indexOfData = array_search($currentData, $allData);
    //     $allData[$indexOfData] = $data;
    //     $out = array_map(fn ($item) => $this->encodeData($item), $allData);
    // }
}
