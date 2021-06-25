<?php

namespace Dwf\PronosticsBundle\Championship;

use Dwf\PronosticsBundle\Entity\Contest;
use Dwf\PronosticsBundle\Entity\Pronostic;
use Dwf\PronosticsBundle\Entity\User;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Symfony\Component\Translation\TranslatorInterface;

class HighchartManager
{
    /** @var Highchart */
    protected $highchart;

    /** @var array */
    protected $series = array();

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * * @param Highchart $highchart
     *
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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
     * @param array  $scores
     *
     * @return Highchart
     */
    public function buildBetsChartWithScores(string $renderTo, string $title, array $scores)
    {
        $data = [];
        $drilldown = [];

        if ($scores['totalScores'] > 0) {
            $data = array(
                array(
                    'name' => $this->translator->trans('Good bets'),
                    'y' => round(((($scores['nbGoodScores'] + $scores['nbPerfectScores']) * 100) / $scores['nbScores'])),
                    'drilldown' => 'goodbets',
                    'visible' => true,
                ),
                array(
                    'name' => $this->translator->trans('Bad bets'),
                    'y' =>round((($scores['nbBadScores'] * 100) / $scores['nbScores'])),
                    'drilldown' => 'badbets',
                    'visible' => true,
                ),
            );
            $drilldown = array(
                ($scores['nbGoodScores'] || $scores['nbPerfectScores']) ? array(
                    'name' => $this->translator->trans('Good bets'),
                    'id' => 'goodbets',
                    'data' => array(
                        array($this->translator->trans('Good bets with perfect scores'),round(($scores['nbPerfectScores'] * 100) / ($scores['nbPerfectScores'] + $scores['nbGoodScores']))),
                        array($this->translator->trans('Good bets'), round(($scores['nbGoodScores'] * 100) / ($scores['nbPerfectScores'] + $scores['nbGoodScores']))),
                    )
                ) : [],
                $scores['nbBadScores'] ? array(
                    'name' => $this->translator->trans('Bad bets'),
                    'id' => 'badbets',
                    'data' => array(
                        array($this->translator->trans('Bad bets'), round((($scores['nbBadScores'] * 100) / $scores['nbBadScores']))),
                    )
                ) : [],
            );
        }

        return $this->pieChart($renderTo, $title, $data, $drilldown);
    }

    /**
     * @param string $renderTo
     * @param string $title
     * @param array  $scores
     *
     * @return Highchart
     */
    public function buildBetPointsChartWithScores(string $renderTo, string $title, array $scores)
    {
        $data = [];
        if ($scores['totalScores'] > 0) {
            $data = array(
                array($this->translator->trans('Total bet points due to perfect scores'), round((($scores['nbPerfectScores'] * Pronostic::NB_POINTS_EXACT_SCORE * 100) / $scores['totalScores']))),
                array($this->translator->trans('Total bet points due to good bets'), round((($scores['nbGoodScores'] * Pronostic::NB_POINTS_GOOD_SCORE * 100) / $scores['totalScores']))),
                array($this->translator->trans('Total bet points due to bad bets'), round((($scores['nbBadScores'] * Pronostic::NB_POINTS_BAD_SCORE * 100) / $scores['totalScores']))),
            );
        }

        return $this->pieChart($renderTo, $title, $data);
    }

    /***
     * @param string     $renderTo
     * @param string     $title
     * @param array      $data
     * @param array|null $drilldown
     *
     * @return Highchart
     */
    public function pieChart(string $renderTo, string $title, array $data, array $drilldown = null)
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
            'showInLegend' => true,
        ));
        $this->series = array(
            array(
                'type' => 'pie',
                'name' => $title,
                'colorByPoint' => true,
                'data' => $data,
            )
        );
        if (is_array($this->series)) {
            $this->highchart->series($this->series);
        }
        if ($drilldown) {
            $this->highchart->drilldown->series($drilldown);
        }

        return $this->highchart;
    }
}