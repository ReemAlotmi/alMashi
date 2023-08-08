<?php
namespace App\Helpers;
class Helper {
    public static function clacDistance($coor1, $coor2) {

        $coor1 = str_replace(["}", "{", "(", ")"], "", $coor1);
        $coor2 = str_replace(["}", "{", "(", ")"], "", $coor2);
    
        $cor1 = explode(",", $coor1);
        $lon1 = $cor1[0];
        $lat1 = $cor1[1];
    
        $cor2 = explode(",", $coor2);
        $lon2 = $cor2[0];
        $lat2 = $cor2[1];
    
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
    
    
        return ($miles * 1.609344);
    
        }


    public static function getDriverRating($driverId){

    }

}

    