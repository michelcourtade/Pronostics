<?php

namespace Dwf\PronosticsBundle\Championship;

use Ob\HighchartsBundle\Highcharts\Highchart;

class HighchartManager {
    
    protected $highchart;
    protected $series = array();
    
    public function __construct()
    {
        $this->highchart = new Highchart();
        $this->highchart->chart->renderTo('chart');
        $this->highchart->chart->type('column');
        $this->highchart->xAxis(array(array('type' => 'category', 'labels' => array('rotation' => -45))));
        $this->highchart->legend->enabled(false);
    }
    
    public function setTitle($title)
    {
        $this->highchart->title->text($title);
    }
    
    public function setSeries($series)
    {
        $this->series = $series;
    }
    
    public function addSerie($serie)
    {
        if(is_array($serie)) {
            $serieToAdd = array(
                'name'  => $serie["name"],
                'type'  => 'column',
                'color' => $serie['color'] ? $serie['color']:'',
                'data'  => $serie['object'],
                'dataLabels' => array('enabled' => true),
            );
            array_push($this->series, $serieToAdd);
        }
    }
    
    public function setYData($data)
    {
        if (is_array($data)) {
            $yData = array(
                        array(
                                'min' => $data['min'],
                                'title' => array(
                                        'text'  => $data['title'],
                                ),
                        ),
                );
            $this->highchart->yAxis($yData);
        }
    }
    public function chart()
    {
        if(is_array($this->series))
            $this->highchart->series($this->series);
        return $this->highchart;
    }
}