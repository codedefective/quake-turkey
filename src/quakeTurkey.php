<?php


namespace codedefective;


namespace codedefective;


use GuzzleHttp\Client;
use /** @noinspection PhpUndefinedClassInspection */
    GuzzleHttp\Exception\GuzzleException;

class quakeTurkey extends Client
{
    private $dataUrl = 'http://www.koeri.boun.edu.tr/scripts/lst0.asp';
    private $client;
    private $response;
    private $contentType = 'utf-8';
    private $contentTag = 'pre';
    private $responseLength = 5;
    private $offsetRow = 7;
    private $endOffsetRow = 9;
    private $responseData;
    private $reverseData = false;
    private $sortedSize = false;
    private $sortedDate = false;
    private $sortedLocation = false;
    private $groupedLocation = false;
    private $groupedDate = false;
    private $jsonType = false;
    private $graphTitle = 'Quake Turkey';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->client = new Client(['cookies' => true,'allow_redirects' => false]);
    }

    private function sort($arr,$var,$subVar=false,$string = false){
        usort($arr,  function( $a, $b ) use($string, $var,$subVar){
            if ($string === true)  {
                if (!$subVar)
                    return strcmp($a[$var], $b[$var]);
                else
                    return strcmp($a[$var][$subVar], $b[$var][$subVar]);
            }
            if (!$subVar)
                return strtotime($b[$var]) - strtotime($a[$var]);
            else
                return strtotime($b[$var][$subVar]) - strtotime($a[$var][$subVar]);
        });
        return $arr;
    }

    private function group($arr,$groupBy,$subName = false){
        $tmp = [];
        $output = [];
        foreach($arr as $arg) {
            if ($subName)
                $tmp[$arg[$groupBy][$subName]][] = $arg;
            else
                $tmp[$arg[$groupBy]][] = $arg;
        }
        foreach($tmp as $type => $labels){
            $output[] = array(
                $groupBy => $type,
                'data' => $labels
            );
        }
        return $output;
    }

    public function toJson(){
        $this->jsonType = true;
        return $this;
    }

    public function setContentTag($tag){
        $this->contentTag = $tag;
        return $this;
    }

    public function setContentType($contentType){
        $this->contentType = $contentType;
        return $this;
    }

    private function setResponseData($data){
        $this->responseData = $data;
    }

    public function limit($limit){
        $this->responseLength = $limit;
        return $this;
    }

    public function reverse(){
        $this->reverseData = true;
        return $this;
    }

    private function formatResponse(){
        preg_match("'<".$this->contentTag.">(.*?)</".$this->contentTag.">'si", $this->getResponse(), $body);
        $rows = explode("\n",$body[1]);
        $length = $this->responseLength ? $this->responseLength : count($rows)-$this->endOffsetRow;
        $slice =  array_slice($rows,$this->offsetRow,$length);

        $this->responseData =  array_map(function ($row){
            preg_match("/([0-9.]{10}\s*)([0-9:]{8}\s*)([0-9.]{1,7}\s*)([0-9.]{1,7}\s*)([0-9.]{1,4}\s*)([0-9.-]{1,4}\s*)([0-9.-]{1,4}\s*)([0-9.-]{1,4}\s*)([a-zA-Z-_()\s]+\s)([a-zA-Z09ğüşöçİĞÜŞÖÇ\S]+\s*)([(][0-9.)]+\s[0-9:]+[)])*/", $row, $data);
            preg_match("/([a-zA-Z0-9-\s]+)([(]+[a-zA-Z0-9\s]+[)])*/",$data[9],$location);
            $data = array_map('trim',$data);
            $dataset = [
                'datetime' => str_replace('.','-',$data[1]) . ' ' . $data[2],
                'date' => str_replace('.','-',$data[1]),
                'time' => $data[2],
                'latitude' => $data[3],
                'longitude' => $data[4],
                'depth' => $data[5],
                'size' => [
                    'md' => $data[6],
                    'ml' => $data[7],
                    'mw' => $data[8],
                ],
                'location' => [
                    'name' => trim($location[1]),
                    'google_url' => 'https://www.google.com/maps/search/'.$data[3].','.$data[4].'/@'.$data[3].','.$data[4].',9z',
                    'description' => isset($location[2]) ? str_replace(['(',')'],'',trim($location[2])) : null
                ],
                'graphData' => [
                    'label' => $data[1] . ' ' . $data[2],
                    'y' =>$data[7],
                    'indexLabel' => trim($location[1]). ' ' . (isset($location[2]) ? str_replace(['(',')'],'',trim($location[2])) : null)
                ],
                'resolution_quality' =>  $data[10],
                'revision_date' =>  isset($data[11]) ? str_replace(['(',')'],'',$data[11]) : null,
            ];
            $dataset['hash'] = hash('ripemd160', json_encode($dataset));
            return $dataset;
        },$slice);
    }

    public function setDataUrl($url){
        $this->dataUrl = $url;
    }

    public function getDataUrl(){
        return $this->dataUrl;
    }

    private function setResponse(){
        try {
            $this->response = mb_convert_encoding($this->client->request('GET', $this->getDataUrl())->getBody(), $this->contentType, 'windows-1254');
        } /** @noinspection PhpUndefinedClassInspection */ catch (GuzzleException $e) {
            echo $e->getMessage();
        }
        return $this;
    }

    public function setGraphTitle($title){
        $this->graphTitle = $title;
        return $this;
    }

    public function getResponse(){
        $this->setResponse();
        return $this->response;
    }

    public function getResponseData(){
        $this->formatResponse();
        return $this->responseData;
    }

    private function createData(){
        if ($this->reverseData === true){
            $this->setResponseData(array_reverse($this->responseData));
        }
        if ($this->jsonType === true){
            $this->setResponseData(json_encode($this->responseData));
        }
        $this->setResponseData($this->getResponseData());
        return $this;
    }

    public function sortBySize($type = 'ml'){
        $this->setResponseData($this->sort($this->getResponseData(),'size',$type));
        $this->sortedSize = true;
        return $this;
    }

    public function sortByDate(){
        $this->setResponseData($this->sort($this->getResponseData(),'datetime'));
        $this->sortedDate = true;
        return $this;
    }

    public function sortByLocation(){
        $this->setResponseData($this->sort($this->getResponseData(),'location','name',true));
        $this->sortedLocation = true;
        return $this;
    }


    public function groupByDate(){
        $this->setResponseData($this->group($this->getResponseData(),'date'));
        $this->groupedDate = true;
        return $this;
    }

    public function groupByLocation(){
        $this->setResponseData($this->group($this->getResponseData(),'location','name'));
        $this->groupedLocation = true;
        return $this;
    }

    private function createHtml($data){
        return '  <!DOCTYPE HTML>
                <html>
                    <head>  
                        <script>
                        window.onload = function () 
                        {

                            var chart = new CanvasJS.Chart("chartContainer", {
                                animationEnabled: true,
                                theme: "light2",
                                title:{
                                    text: "'.$this->graphTitle.'"
                                },
                                axisX:{
                                    crosshair: {
                                        enabled: true,
                                        snapToDataPoint: true
                                    }
                                },
                                axisY:{
                                    title: "Size/Ml",
                                    suffix: "ml",
                                    crosshair: {
                                        enabled: true,
                                        snapToDataPoint: true
                                    },
                                    scaleBreaks: {
                                        autoCalculate: true
                                    }
                                },
                                toolTip:{
                                    enabled: false
                                },
                                data: [{
                                    type: "column",
                                    indexLabelPlacement: "inside",
		                            indexLabelFontColor: "white",
		                            indexLabelFontSize: "11",
		                            indexLabelMaxWidth: "100",
                                    dataPoints:' . $data . '
                                }]
                            });
                            chart.render();
                        }
                        </script>
                    </head>
                    <body>
                        <div id="chartContainer" style="height: 370px; width: 100%;"></div>
                        <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
                    </body>
                </html>';
    }

    public function createGraphic(){
        $this->createData();
         return $this->createHtml(json_encode(array_column($this->responseData, 'graphData'),JSON_NUMERIC_CHECK |JSON_UNESCAPED_SLASHES));
    }

    public function getList(){
        $this->createData();
        return $this->responseData;
    }
}