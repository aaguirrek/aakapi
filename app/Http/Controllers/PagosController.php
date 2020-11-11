<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Curl;
use App\Search;
use DB;
class PagosController extends Controller
{
    public function culqi_charge(Request $request){
        $culqi=[];
        $culqi["token"] = $request->token;
        $culqi["amount"] = $request->amount;
        $culqi["email"] = $request->email;
        $culqi["currency_code"] = $request->currency_code;
        $curl = Curl::to( "https://hisalud.com/api/method/culqipos.api.cargo")
            ->withHeader("Content-Type: application/json" )
            ->withHeader("Authorization: Basic MjM0NWQ0OWEwOWM2MTgwOmRkYTcyNTQ5MjZkOWY2Nw==" )
            ->withData( json_encode($culqi) )
            ->post();
        return $curl;
    }
    public function search(Request $request){
        $filters="";

        foreach ($request->except(["precio_min","precio_max","cookie"]) as $key => $value) {
            $value = str_replace("á","%",$value);
            $value = str_replace("é","%",$value);
            $value = str_replace("í","%",$value);
            $value = str_replace("ó","%",$value);
            $value = str_replace("ú","%",$value);
            $value = str_replace("ñ","%",$value);
            if($key == "especialidad" ||  
            $key == "provincia" || 
            $key == "distrito" || 
            $key == "departamento" ||
            $key == "ubicacion" ||
            $key == "centro_de_labores"
            ){
                $filters.= "%\"".$key."%\": \"".$value."\"%";
            }else{
                if($key=="nombre" || $key == "apellidos"){
                    $filters.= "%\"".$key."\": \""."%".strtoupper($value)."%"."\"%";
                }else{
                    $filters.= "%\"".$key."\": \"".$value."\"%";
                }
            }
        }
        if($filters!=""){
           return  Search::where("data","like",$filters)->paginate();
        }else{
            if(!$request->exists('precio_min')){

                return  Search::paginate();
            }
        }
        if($request->exists('precio_min')){
            if($filters!=""){
                return Search::where("data","like",$filters)->where("precio","<=",$request->precio_max)->where("precio",">=",$request->precio_min)->paginate();
            }else{
                return Search::where("precio","<=",$request->precio_max)->where("precio",">=",$request->precio_min)->paginate();
            }
        }
    }
    function get_all($name,Request $request=null){
        $data=[];
        $database = "mysql";
        if($request->exists('domain')){
            if($request->domain === "lyndaerp.frappe.technology"){
                $database = "mikuarto";
            }
        }
        
        
        $data["docType"]= DB::connection($database)->table('tabDocType')->where('name',$name)->first();
        $data["docPerm"]= DB::connection($database)->table('tabDocPerm')->where('parent',$name)->get();
        return $data;
    }
    function get_list_table($name,Request $request){
        $database = "mysql";
        if($request->exists('domain')){
            if($request->domain === "lyndaerp.frappe.technology"){
                $database = "mikuarto";
            }
        }
        
        return DB::connection($database)->table('tab'.$name)->all();
    }
    function get_all_table($name,Request $request){
        $request->email;
        $lvlpermission=0;
        if($request->exists('permissions')){
            $data=[];
            $data["doctype"]=$name;
            $data["ptype"]=$request->permissions;
            $curl = Curl::to( "https://".$request->domain."/api/method/algolia.api.checkpermissions")
            ->withHeader("Content-Type: application/json" )
            ->withOption("COOKIE", $request->cookie )
            ->withData( json_encode( $data ))
            ->post();
            $curl = json_decode($curl);
            $lvlpermission = $curl->message->hasPermission;
        }
        $database = "mysql";
        if($request->exists('domain')){
            if($request->domain === "lyndaerp.frappe.technology"){
                $database = "mikuarto";
            }
        }
        $table;
        if($name !== "viewDocField"){

            $table = DB::connection($database)->table('tab'.$name);
        }else{
            $table = DB::connection($database)->table($name);
        }
        if($request->exists('fields')){
            $table->select($request->fields);
        }
        if($request->exists('filters')){
            foreach ($request->filters as $key => $value) {
                $table->where($value[0],$value[1],$value[2]);
            }
        }
        if($lvlpermission === 1){
            $table->where("owner",$curl->message->user);
        }
        if($lvlpermission === false){
            return [];
        }
        if($name === "DocField"){
            $table->orderBy("idx");
        }else{
            if($name !== "viewDocField"){
                $table->orderByDesc("modified");
            }
        }
        if($request->exists('paginate')){
            return $table->paginate();
        }
        return $table->get();

    }
}
