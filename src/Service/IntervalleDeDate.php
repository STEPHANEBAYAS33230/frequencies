<?php


namespace App\Service;


class IntervalleDeDate
{
    public function dateDiff($date1){
        //INTERVAL EN MINUTES entre une date et now
        $date2=new \DateTime('now');
        $interval = $date1->diff($date2);
        $minutes = $interval->format('%a') * 24 * 60;
        $minutes += $interval->format('%h') * 60;
        $minutes += $interval->format('%i');
        return $minutes;
    }
}