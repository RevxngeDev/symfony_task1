<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    #[Route('/calendar/{viewType}/{month}', name: 'calendar', defaults: ['viewType' => 'table', 'month' => null])]
    public function index(string $viewType = 'table', ?int $month = null): Response
    {
        $month = $month ?? (int) date('m');
        $year = (int) date('Y');

        $currentDate = new \DateTime();

        $days = [];
        $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($day = 1; $day <= $totalDays; $day++) {
            $date = \DateTime::createFromFormat('Y-m-d', "$year-$month-$day");
            $days[] = [
                'day' => $day,
                'weekday' => $date->format('l'),
                'isWeekend' => in_array($date->format('N'), [6, 7]), // Sábado o Domingo
                'isPast' => $date < $currentDate, // Verificar si es un día pasado
            ];
        }
        $calendar = [];
        $firstDayOfMonth = (int) \DateTime::createFromFormat('Y-m-d', "$year-$month-1")->format('N');
        $currentWeek = array_fill(0, $firstDayOfMonth - 1, null);

        foreach ($days as $day) {
            $currentWeek[] = $day;
            if (count($currentWeek) === 7) {
                $calendar[] = $currentWeek;
                $currentWeek = [];
            }
        }
        if (!empty($currentWeek)) {
            $currentWeek = array_pad($currentWeek, 7, null);
            $calendar[] = $currentWeek;
        }

        $template = match ($viewType) {
            'table' => 'calendar/table.html.twig',
            'list' => 'calendar/list.html.twig',
            'weekends' => 'calendar/weekends.html.twig',
            default => 'calendar/table.html.twig',
        };

        return $this->render($template, [
            'calendar' => $calendar,
            'month' => $month,
            'year' => $year,
        ]);
    }

}
