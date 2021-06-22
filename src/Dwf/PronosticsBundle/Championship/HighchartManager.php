<?php

namespace Dwf\PronosticsBundle\Championship;

use Ob\HighchartsBundle\Highcharts\Highchart;

class HighchartManager
{
    /** @var Highchart */
    protected $highchart;

    /** @var array */
    protected $series = array();

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->highchart->title->text($title);
    }

    /**
     * @param array $series
     */
    public function setSeries(array $series)
    {
        $this->series = $series;
    }

    /**
     * @param array $serie
     */
    public function addSerie(array $serie)
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

    /**
     * @param array $data
     */
    public function setYData(array $data)
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

    /**
     * @return Highchart
     */
    public function chart()
    {
        $this->highchart = new Highchart();
        $this->highchart->chart->renderTo('chart');
        $this->highchart->chart->type('column');
        $this->highchart->xAxis(array(array('type' => 'category', 'labels' => array('rotation' => -45))));
        $this->highchart->legend->enabled(false);
        if(is_array($this->series))
            $this->highchart->series($this->series);
        return $this->highchart;
    }

    /**
     * @param string $renderTo
     * @param string $title
     * @param array  $data
     *
     * @return Highchart
     */
    public function pieChart(string $renderTo, string $title, array $data)
    {
        $this->highchart = new Highchart();
        $this->highchart->chart->renderTo($renderTo);
        $this->highchart->title->text($title);
        $this->highchart->tooltip->pointFormat('{point.name}: <b>{point.y:.2f}%</b><br/>');
        $this->highchart->plotOptions->pie(array(
            'allowPointSelect' => true,
            'cursor' => 'pointer',
            'dataLabels' => array(
                'enabled' => true,
                'format' => '{point.name}: {point.y:.1f}%',
            ),
            'showInLegend' => false
        ));
        $this->series = array(array(
            'type' => 'pie',
            'name' => $title,
            'data' => $data,
            )
        );
        if (is_array($this->series)) {
            $this->highchart->series($this->series);
        }

        return $this->highchart;
    }
}