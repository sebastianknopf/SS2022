<?php

use LessQL\Database;

function demand_database()
{
    $pdo = new PDO('sqlite:./data/demand.db3');
    return new Database($pdo);
}

function stop_database()
{
    $pdo = new PDO('sqlite:./data/stops.db3');
    return new Database($pdo);
}

function find_routes()
{
    $db = demand_database();

    $db->setPrimary('demand', 'route');

    return array_map(function ($r) {
        return $r->route;
    }, $db->demand()->select('DISTINCT route AS route')->orderBy('route')->fetchAll());
}

function find_stops()
{
    return stop_database()->stop()->fetchAll();
}

function find_matching_trip_demand_data($dateFrom, $dateUntil, $routeName, $direction, $dayType)
{
    $db = new PDO('sqlite:./data/demand.db3');
    $stmt = null;

    if ($direction != 0) {
        $stmt = $db->prepare("SELECT route, direction, start_time AS 'startTime', stop_code AS 'stopCode', MAX(num_psg_occupation) AS 'maxOccupation', AVG(num_vehicles) AS 'num_vehicles', AVG(capacity) AS 'capacity' FROM demand WHERE capacity IS NOT NULL AND start_time IS NOT NULL AND date >= :dateFrom AND date <= :dateUntil AND daytype = :dayType AND route = :routeName GROUP BY route, direction, start_time HAVING MIN(rowid) ORDER BY route, direction, start_time");
        $stmt->execute([
            'dateFrom' => $dateFrom,
            'dateUntil' => $dateUntil,
            'routeName' => $routeName,
            // 'direction' => $direction,
            'dayType' => $dayType
        ]);
    } else {
        $stmt = $db->prepare("SELECT route, direction, start_time AS 'startTime', stop_code AS 'stopCode', MAX(num_psg_occupation) AS 'maxOccupation', AVG(num_vehicles) AS 'num_vehicles', AVG(capacity) AS 'capacity' FROM demand WHERE capacity IS NOT NULL AND start_time IS NOT NULL AND date >= :dateFrom AND date <= :dateUntil AND daytype = :dayType AND route = :routeName GROUP BY route, direction, start_time HAVING MIN(rowid) ORDER BY route, direction, start_time");
        $stmt->execute([
            'dateFrom' => $dateFrom,
            'dateUntil' => $dateUntil,
            'routeName' => $routeName,
            'dayType' => $dayType
        ]);
    }

    $results = [];
    foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $result) {
        array_push($results, array_merge($result, [
            'occupationLevel' => $result['maxOccupation'] / $result['capacity']
        ]));
    }

    return $results;
}

function find_matching_map_demand_data($dateFrom, $dateUntil, $routeName, $direction, $dayType)
{
    // fetch stops at first
    $stops = find_stops();

    // fetch map data
    $db = new PDO('sqlite:./data/demand.db3');
    $stmt = null;

    if ($direction != 0) {
        $stmt = $db->prepare("SELECT route, direction, stop_id AS 'stopId', MAX(num_psg_occupation) AS 'maxOccupation', AVG(num_vehicles) AS 'numVehicles', AVG(capacity) AS 'capacity' FROM demand WHERE capacity IS NOT NULL AND start_time IS NOT NULL AND date >= :dateFrom AND date <= :dateUntil AND daytype = :dayType AND route = :routeName AND direction = :direction GROUP BY route, direction, stop_id ORDER BY route, direction, stop_index");
        $stmt->execute([
            'dateFrom' => $dateFrom,
            'dateUntil' => $dateUntil,
            'routeName' => $routeName,
            'direction' => $direction,
            'dayType' => $dayType
        ]);
    } else {
        $stmt = $db->prepare("SELECT route, direction, stop_id AS 'stopId', MAX(num_psg_occupation) AS 'maxOccupation', AVG(num_vehicles) AS 'numVehicles', AVG(capacity) AS 'capacity' FROM demand WHERE capacity IS NOT NULL AND start_time IS NOT NULL AND date >= :dateFrom AND date <= :dateUntil AND daytype = :dayType AND route = :routeName GROUP BY route, direction, stop_id ORDER BY route, direction, stop_index");
        $stmt->execute([
            'dateFrom' => $dateFrom,
            'dateUntil' => $dateUntil,
            'routeName' => $routeName,
            'dayType' => $dayType
        ]);
    }

    // parse results into container
    $results = [];

    $lastResult = null;
    foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $result) {
        if ($lastResult != null && $lastResult['route'] == $result['route'] && $lastResult['direction'] == $lastResult['direction']) {

            $fromStop = array_values(array_filter($stops, function ($stop) use ($lastResult) {
                if ($stop['id'] == $lastResult['stopId']) {
                    return true;
                } else {
                    return false;
                }
            }))[0];

            $toStop = array_values(array_filter($stops, function ($stop) use ($result) {
                if ($stop['id'] == $result['stopId']) {
                    return true;
                } else {
                    return false;
                }
            }))[0];

            array_push($results, [
                'fromStop' => $fromStop,
                'toStop' => $toStop,
                'direction' => $lastResult['direction'],
                'maxOccupation' => $lastResult['maxOccupation'],
                'occupationLevel' => $lastResult['maxOccupation'] / $lastResult['capacity'],
                'numVehicles' => $lastResult['numVehicles'],
                'capacity' => $lastResult['capacity']
            ]);
        }

        $lastResult = $result;
    }

    return $results;
}