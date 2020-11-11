<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Curl;
use Storage;
class PostApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function DNI($dni)
    {
        

            $curl = Curl::to( "https://lobellum.frappe.technology/api/services/dni/".$dni)
            ->withHeader("Content-Type: application/json" )
            ->withHeader("Authorization: Bearer m5TX5llKHKx3WhIGBqNqX3VLozorFcz7yBxtpAWXGFojX7brWA" )
            ->get();
            
            return $curl;
    }
    public function Appointment(Request $request)
    {
        $data=[
            "mode_of_payment"=> "Tarjeta de Crédito",
            "appointment_type"=>"Cita por temas varios",
            "duration" => "30",
            "patient" => $request->patient,
            "appointment_datetime" => $request->appointment_date." ".$request->appointment_time,
            "paid_amount" => (float)$request->monto,
            "appointment_date"=>$request->appointment_date,
            "appointment_time"=>$request->appointment_time,
            "department"=>$request->area,
            "practitioner"=>$request->practitioner
        ];
        if ($request->filled('motivo_de_cita')) {
            $data['motivo_de_cita'] = $request->motivo_de_cita;
        }
        
        $curl = Curl::to( "https://hisalud.com/api/resource/Patient Appointment")
            ->withHeader("Content-Type: application/json" )
            ->withOption("COOKIE", $request->cookie )
            ->withData( json_encode( $data ))
            ->post();
        return $curl;
    }

    public function create_web_user(Request $request){
        $login=[];
        $login["email"] = $request->email;
        $login["nombre"] = $request->nombre;
        $login["apellido"] = $request->apellidos;
        $login["contrasena"] = $request->contrasena;
        $login["sexo"] = $request->sexo;
        $curl = Curl::to( "https://hisalud.com/api/method/telemedicina.api.createWebUser")
            ->withHeader("Content-Type: application/json" )
            ->withHeader("Authorization: Basic MjM0NWQ0OWEwOWM2MTgwOmRkYTcyNTQ5MjZkOWY2Nw==" )
            ->withData( json_encode($login) )
            ->post();
        return $curl;
            
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPatient(Request $request)
    {
        
        $data=[
            "user_id"=> $request->user
        ];

        $curl = Curl::to( "https://hisalud.com/api/method/telemedicina.api.get_patient")
            ->withHeader("Content-Type: application/json" )
            ->withOption("COOKIE", $request->cookie )
            ->withData( $data )
            ->returnResponseObject()
            ->withResponseHeaders()
            ->asJson()
            ->post();
        return response()->json($curl, 200);
    }

    public function createUser(Request $request){

    }
    public function any(Request $request, $doctype=null,$name=""){
        $params="";
        $initurl="";
        $method="get";
        $domain="hisalud.com";
        if($name!=""){
            $name= "/".$name;
        }
        $initurl= "/api/resource/".$doctype;
        if( $request->exists('method')){
            $method = $request->method;
        }
        $data = $request->except(['domain','cookie','method']);
        $lastElement = end($data );
        $params.="?";
        foreach ($data as $key => $value) {
            $params .= $key."=". str_replace(" ","%20", json_encode($value ) ) ;
            if($value !== $lastElement) {
                $params .="&";
            }
        }
        
        $curl=null;
        
        if($request->method =="post"){
            $curl = Curl::to( "https://".$request->domain.$initurl.$name.$params )
            ->withHeader("Content-Type: application/json" )
            ->withOption("COOKIE", $request->cookie )
            ->withData( json_encode( $data ))
            ->post();
        }
        if($request->method =="get"){
            $curl = Curl::to( "https://".$request->domain.$initurl.$name.$params )
            ->withHeader("Content-Type: application/json" )
            ->withOption("COOKIE", $request->cookie )
            ->get();
        }
        if($request->method =="put"){
            $curl = Curl::to( "https://".$request->domain.$initurl.$name.$params )
            ->withHeader("Content-Type: application/json" )
            ->withOption("COOKIE", $request->cookie )
            ->withData( json_encode( $data ))
            ->put();
        }
        if($request->method =="delete"){
            $curl = Curl::to( "https://".$request->domain.$initurl.$name.$params )
            ->withHeader("Content-Type: application/json" )
            ->withOption("COOKIE", $request->cookie )
            ->delete();
        }
        $resp = json_decode($curl);
        if(is_object($resp) || is_array($resp) ){
            return response()->json($resp, 200); 
        }else{
            return $curl;
        }
        
         
    }
    public function metodo(Request $request, $method=""){
        $initurl= "/api/method/".$method;
        
        $data = $request->except(['domain','cookie']);
        $curl=null;
        
        if( $request->exists('cookie')){
            $curl = Curl::to( "https://".$request->domain.$initurl )
            ->withHeader("Content-Type: application/json" )
            ->withOption("COOKIE", $request->cookie )
            ->withData( json_encode( $data ))
            ->post();
        }else{
            $curl = Curl::to( "https://".$request->domain.$initurl )
            ->withHeader("Content-Type: application/json" )
            ->withData( json_encode( $data ))
            ->post();
        }
        $resp = json_decode($curl);
        if(is_object($resp) || is_array($resp) ){
            return response()->json($resp, 200); 
        }else{
            return $curl;
        }
        
         
    }

    public function file_upload(Request $request){
        $files = Storage::allFiles('public');
        Storage::delete($files);
        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        
        $initurl= "/api/method/upload_file";
        
        $t=$request->file('file')->getClientOriginalName();
        $content = $request->file('file')->get();
        
        Storage::disk('public')->put($t, $content);
        $data = $request->except(['domain','cookie', 'file','method']);
        $curl=null;
        $minetype=Storage::disk('public')->put($t, $content);
        $ext = strtolower($request->file('file')->getClientOriginalExtension());
        if (array_key_exists($ext, $mime_types)) {
            $minetype = $mime_types[$ext];
        }
        if( $request->exists('cookie')){
            $curl = Curl::to( "https://".$request->domain.$initurl )
            ->withHeader("Content-Type: multipart/form-data" )
            ->withHeader("Accept: application/json" )
            ->withOption("COOKIE", $request->cookie )
            ->withData(  $data )
            ->withFile('file', Storage::disk('public')->path($t), $minetype, $t)
            ->post();
        }else{
            $curl = Curl::to( "https://".$request->domain.$initurl )
            ->withHeader("Content-Type: multipart/form-data" )
            ->withHeader("Accept: application/json" )
            ->withData(  $data )
            ->withFile('file', Storage::disk('public')->path($t), $minetype, $t)
            ->post();
        }
        $resp = json_decode($curl);
        
        if(is_object($resp) || is_array($resp) ){
            return response()->json($resp, 200); 
        }else{
            return $curl;
        }
    }
    
    public function storePatient(Request $request)
    {

        $data=[
            "sex"=> $request->sex,
            "dni"=> $request->dni,
            "blood_group"=> $request->blood_group,
            "dob"=> $request->dob,
            "status"=> "Active",
            "report_preference"=> $request->report_preference,
            "mobile"=> $request->mobile,
            "email"=> $request->email,
            "phone"=> $request->phone,
            "apellidos_y_nombres_del_contacto"=> $request->apellidos_y_nombres_del_contacto,
            "celular_del_contacto"=> $request->celular_del_contacto,
            "relacion_del_contacto"=>$request->relacion_del_contacto,
            "celular_del_contacto"=>$request->celular_del_contacto,
            "telefono_del_contacto"=>$request->telefono_del_contacto,
            "email_del_contacto"=>$request->email_del_contacto,
            

        ];
        if($request->name != "__nuevo__" && $request->name != ""){
            $curl = Curl::to( "https://hisalud.com/api/resource/Patient/".$request->name)
                ->withHeader("Content-Type: application/json" )
                ->withOption("COOKIE", $request->cookie )
                ->withData( $data )
                ->returnResponseObject()
                ->withResponseHeaders()
                ->asJson()
                ->put();
        }else{
            $curl = Curl::to( "https://hisalud.com/api/resource/Patient")
                ->withHeader("Content-Type: application/json" )
                ->withOption("COOKIE", $request->cookie )
                ->withData( $data )
                ->returnResponseObject()
                ->withResponseHeaders()
                ->asJson()
                ->post();
        }
        return ["data"=>$data, "cookie"=>$request->cookie, "url"=> "https://hisalud.com/api/resource/Patient/".$request->name];
    }
    
    public function storePatient2(Request $request)
    {
        $data=[
            "allergies"=> $request->allergies,
            "medical_history"=> $request->medical_history,
            "medication"=> $request->medication,
            "surgical_history"=> $request->surgical_history,
            "tobacco_past_use"=> "",
            "tobacco_current_use"=> $request->tobacco_current_use,
            "alcohol_past_use"=> "",
            "alcohol_current_use"=> $request->alcohol_current_use,
            "surrounding_factors"=> $request->surrounding_factors,
            "other_risk_factor"=> $request->other_risk_factor,
            "patient_details"=> $request->patient_details,
            "consumo_otras_drogas"=> $request->consumo_otras_drogas,
            "consumo_marihuana"=> $request->consumo_marihuana,
            "talla"=>$request->talla,            
            "peso"=>$request->peso
        ];

        $curl = Curl::to( "https://hisalud.com/api/resource/Patient/".$request->name)
            ->withHeader("Content-Type: application/json" )
            ->withOption("COOKIE", $request->cookie )
            ->withData( $data )
            ->returnResponseObject()
            ->withResponseHeaders()
            ->asJson()
            ->put();
        return response()->json($curl, 200);
    }

    public function filters(Request $request)
    {
        $login=[];
        if($request->nombre != ""){
            
            $login["nombre"] = strtoupper($request->nombre);
        }
        if($request->apellidos != ""){
            $login["apellidos"] = strtoupper($request->apellidos);
        }
        if($request->departamento != ""){
            $login["departamento"] = $request->departamento;
        }
        if($request->provincia != ""){
            $login["provincia"] = $request->provincia;
        }
        if($request->distrito != ""){
            $login["distrito"] = $request->distrito;
        }
        if($request->especialidad != ""){
            $login["especialidad"] = strtoupper( $request->especialidad);
        }
        if($request->tiempo != "" && $request->tiempo != null){
            $login["tiempo"] = $request->tiempo;
        }
        if($request->estado != "" && $request->estado != null){
            $login["estado"] = $request->estado;
        }
        if($request->dia != "" && $request->dia != null){
            $login["dia"] = $request->dia;
        }
        $login["start_limit"] = $request->start_limit;
        $login["limit_end"] = $request->limit_end;
        $curl = Curl::to( "https://hisalud.com/api/method/telemedicina.api.get_medicos_filtros")
            ->withHeader("Content-Type: application/json" )
            ->withOption("COOKIE", $request->cookie )
            ->withData( json_encode($login) )
            ->post();

        return $curl;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
