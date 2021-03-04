<?php

namespace App\Http\Controllers\Api\FacturaPayload;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FacturaPayload;
Use Exception;
use Illuminate\Support\Facades\DB;
use stdClass;

use App\Classes\Utilities;

class FacturaPayloadController extends Controller
{
    public function get(Request $request, $pais) {
        $id = $request['IDFactura'];
        $id_restaurante = $request['IDRestaurante'];
        $id_cadena = $request['IDCadena'];
        if ($id == null) {
           $factura_payloads = FacturaPayload::where('IDCadena', $id_cadena)->where('IDRestaurante', $id_restaurante)->get();
           return response()->json($factura_payloads,200);
        } else {
           $factura_payload = FacturaPayload::where('IDCadena', $id_cadena)->where('IDRestaurante', $id_restaurante)->where('IDFactura', $id)->first();
           if ($factura_payload) {
               return response()->json($factura_payload,200);
           } else {
               return response()->json("factura no encontrada",400);
           }
        }
    }

    public function post(Request $request, $pais) {
        $data = $request->json()->all();
        $new_id_factura = uniqid();
        $id_restaurante = $data['IDRestaurante'];
        $id_cadena = $data['IDCadena'];
        $id_menu = $data['IDMenu'];
        $new_detalle = $data['detalle'];
        $new_modificadores = $data['modificadores'];
        $new_cabecera = $data['cabecera'];
        $new_formasPago = $data['formasPago'];
        $detalle_to_insert = [];
        $formas_pago_to_insert = [];
        $modificadores_to_insert = [];
        $utilities = new Utilities();
        if ($new_detalle !== []) {
            foreach($new_detalle as $item_to_insert) {
                $item_to_insert['codigoApp'] = $new_id_factura;
                $validation = $utilities->check_if_detalle($item_to_insert);
                if ($validation->pass == false) {
                    return response()->json($validation,400);
                }
                array_push($detalle_to_insert, $item_to_insert);
            }
        }
        if ($new_modificadores !== []) {
            foreach($new_modificadores as $item_to_insert) {
                $validation = $utilities->check_if_modificador($item_to_insert);
                if ($validation->pass == false) {
                    return response()->json($validation,400);
                }
                array_push($modificadores_to_insert, $item_to_insert);
            }
        }
        if ($new_cabecera !== []) {
            $new_cabecera['codigoApp'] = $new_id_factura;
            $validation = $utilities->check_if_cabecera($new_cabecera);
            if ($validation->pass == false) {
                return response()->json($validation,400);
            }
        }
        if ($new_formasPago !== []) {
            foreach($new_formasPago as $new_formaPago) {
                $new_formaPago['codigoApp'] = $new_id_factura;
                $validation = $utilities->check_if_formas_pago($new_formaPago);
                if ($validation->pass == false) {
                    return response()->json($validation,400);
                }
                array_push($formas_pago_to_insert, $new_formaPago);
            }
        }
        $new_factura_payload = new FacturaPayload();
        if ($new_cabecera != []) {
            $new_cabecera = json_encode($new_cabecera);
        }
        if ($detalle_to_insert != []) {
            $detalle_to_insert = json_encode($detalle_to_insert);
        }
        if ($modificadores_to_insert != []) {
            $modificadores_to_insert = json_encode($modificadores_to_insert);
        }
        if ($formas_pago_to_insert != []) {
            $formas_pago_to_insert = json_encode($formas_pago_to_insert);
        }
        $new_factura_payload->cabecera = $new_cabecera;
        $new_factura_payload->detalle = $detalle_to_insert;
        $new_factura_payload->modificadores = $modificadores_to_insert;
        $new_factura_payload->formasPago = $formas_pago_to_insert;
        $new_factura_payload->status = 'activo';
        $new_factura_payload->IDFactura = $new_id_factura;
        $new_factura_payload->IDMenu = $id_menu;
        $new_factura_payload->IDRestaurante = $id_restaurante;
        $new_factura_payload->IDCadena = $id_cadena;
        $new_factura_payload->save();
        return response()->json($new_id_factura,200);
    }

    public function put(Request $request, $pais) {
        try{
            DB::beginTransaction();
            $data = $request->json()->all();
            $new_detalle = $data['detalle'];
            $new_modificadores = $data['modificadores'];
            $new_cabecera = $data['cabecera'];
            $new_formasPago = $data['formasPago'];
            $detalle_to_insert = [];
            $formas_pago_to_insert = [];
            $modificadores_to_insert = [];
            $utilities = new Utilities();
            if ($new_detalle !== []) {
                foreach($new_detalle as $item_to_insert) {
                    $item_to_insert['codigoApp'] = $data['IDFactura'];
                    $validation = $utilities->check_if_detalle($item_to_insert);
                    if ($validation->pass == false) {
                        return response()->json($validation,400);
                    }
                    array_push($detalle_to_insert, $item_to_insert);
                }
            }
            if ($new_modificadores !== []) {
                foreach($new_modificadores as $item_to_insert) {
                    $validation = $utilities->check_if_modificador($item_to_insert);
                    if ($validation->pass == false) {
                        return response()->json($validation,400);
                    }
                    array_push($modificadores_to_insert, $item_to_insert);
                }
            }
            if ($new_cabecera !== []) {
                $new_cabecera['codigoApp'] = $data['IDFactura'];
                $validation = $utilities->check_if_cabecera($new_cabecera);
                if ($validation->pass == false) {
                    return response()->json($validation,400);
                }
            }
            if ($new_formasPago !== []) {
                foreach($new_formasPago as $new_formaPago) {
                    $new_formaPago['codigoApp'] = $data['IDFactura'];
                    $validation = $utilities->check_if_formas_pago($new_formaPago);
                    if ($validation->pass == false) {
                        return response()->json($validation,400);
                    }
                    array_push($formas_pago_to_insert, $new_formaPago);
                }
            }
            $factura_payload = FacturaPayload::where('IDCadena', $data['IDCadena'])->where('IDRestaurante', $data['IDRestaurante'])->where('IDFactura', $data['IDFactura'])->update([
                'cabecera'=>json_encode($new_cabecera),
                'detalle'=>json_encode($detalle_to_insert),
                'modificadores'=>json_encode($modificadores_to_insert),
                'formasPago'=>json_encode($formas_pago_to_insert),
                'IDMenu'=>$data['IDMenu'],
                'status'=>$data['status'],
            ]);
            DB::commit();
            return response()->json(true,200);
         } catch (Exception $e) {
            return response()->json($e->getMessage(),400);
         }
    }

    public function put_cabecera(Request $request, $pais) {
        try{
            DB::beginTransaction();
            $data = $request->json()->all();
            $new_cabecera = $data['cabecera'];
            $utilities = new Utilities();
            if ($new_cabecera !== []) {
                $new_cabecera['codigoApp'] = $data['IDFactura'];
                $validation = $utilities->check_if_cabecera($new_cabecera);
                if ($validation->pass == false) {
                    return response()->json($validation,400);
                }
            }
            $factura_payload = FacturaPayload::where('IDCadena', $data['IDCadena'])->where('IDRestaurante', $data['IDRestaurante'])->where('IDFactura', $data['IDFactura'])->update([
                'cabecera'=>json_encode($new_cabecera),
            ]);
            DB::commit();
            return response()->json(true,200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(),400);
        }
    }

    public function put_formasPago(Request $request, $pais) {
        try{
            DB::beginTransaction();
            $data = $request->json()->all();
            $new_formasPago = $data['formasPago'];
            $formas_pago_to_insert = [];
            $utilities = new Utilities();
            if ($new_formasPago !== []) {
                foreach($new_formasPago as $new_formaPago) {
                    $new_formaPago['codigoApp'] = $data['IDFactura'];
                    $validation = $utilities->check_if_formas_pago($new_formaPago);
                    if ($validation->pass == false) {
                        return response()->json($validation,400);
                    }
                    array_push($formas_pago_to_insert, $new_formaPago);
                }
            }
            $factura_payload = FacturaPayload::where('IDCadena', $data['IDCadena'])->where('IDRestaurante', $data['IDRestaurante'])->where('IDFactura', $data['IDFactura'])->update([
                'formasPago'=>json_encode($formas_pago_to_insert),
            ]);
            DB::commit();
            return response()->json(true,200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(),400);
        }
    }

    public function put_detalle(Request $request, $pais) {
        try{
            DB::beginTransaction();
            $data = $request->json()->all();
            $new_detalle = $data['detalle'];
            $new_modificadores = $data['modificadores'];
            $detalle_to_insert = [];
            $modificadores_to_insert = [];
            $utilities = new Utilities();
            if ($new_detalle !== []) {
                foreach($new_detalle as $item_to_insert) {
                    $item_to_insert['codigoApp'] = $data['IDFactura'];
                    $validation = $utilities->check_if_detalle($item_to_insert);
                    if ($validation->pass == false) {
                        return response()->json($validation,400);
                    }
                    array_push($detalle_to_insert, $item_to_insert);
                }
            }
            if ($new_modificadores !== []) {
                foreach($new_modificadores as $item_to_insert) {
                    $validation = $utilities->check_if_modificador($item_to_insert);
                    if ($validation->pass == false) {
                        return response()->json($validation,400);
                    }
                    array_push($modificadores_to_insert, $item_to_insert);
                }
            }
            $factura_payload = FacturaPayload::where('IDCadena', $data['IDCadena'])->where('IDRestaurante', $data['IDRestaurante'])->where('IDFactura', $data['IDFactura'])->update([
                'detalle'=>json_encode($detalle_to_insert),
                'modificadores'=>json_encode($modificadores_to_insert),
            ]);
            DB::commit();
            return response()->json(true,200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(),400);
        }
    }

    public function delete(Request $request, $pais) {
        try{
            DB::beginTransaction();
            $factura_payload = FacturaPayload::where('IDCadena', $request['IDCadena'])->where('IDRestaurante', $request['IDRestaurante'])->where('IDFactura', $request['IDFactura'])->update([
                'status'=>'inactivo',
            ]);
            DB::commit();
            return response()->json(true,200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(),400);
        }
    }

    public function inject_payload(Request $request, $pais) {
        $id_cadena = $request['IDCadena'];
        $id_restaurante = $request['IDRestaurante'];
        $id_factura = $request['IDFactura'];
        $endpoint = DB::select('SELECT endpoint FROM conexiones WHERE prefijo_pais = :prefijo_pais', ['prefijo_pais'=>$pais])[0]->endpoint;
        $utilities = new Utilities();
        $factura_payload = FacturaPayload::where('IDCadena', $id_cadena)->where('IDRestaurante', $id_restaurante)->where('IDFactura', $id_factura)->first();
        $validation = $this->validate_factura_payload($factura_payload);
        if ($validation->pass) {
            $data_to_send = json_encode($factura_payload);
            $url = $endpoint;
            $response = json_decode($utilities->httpPost($url, $data_to_send));
            return response()->json(["respuesta"=>$response, "payload"=>$factura_payload],200);
        }
        return response()->json($validation,400);
    }

    protected function validate_factura_payload($factura_payload) {
        $utilities = new Utilities();
        try{
            $new_detalle = $factura_payload->detalle;
            $new_modificadores = $factura_payload->modificadores;
            $new_cabecera = $factura_payload->cabecera;
            $new_formasPago = $factura_payload->formasPago;
            if ($new_detalle !== []) {
                foreach($new_detalle as $item_to_insert) {
                    $validation = $utilities->check_if_detalle($item_to_insert);
                    if ($validation->pass == false) {
                        return $validation;
                    }
                }
            }
            if ($new_modificadores !== []) {
                foreach($new_modificadores as $item_to_insert) {
                    $validation = $utilities->check_if_modificador($item_to_insert);
                    if ($validation->pass == false) {
                        return $validation;
                    }
                }
            }
            if ($new_cabecera !== []) {
                $validation = $utilities->check_if_cabecera($new_cabecera);
                if ($validation->pass == false) {
                    return $validation;
                }
            }
            if ($new_formasPago !== []) {
                foreach($new_formasPago as $forma_pago) {
                    $validation = $utilities->check_if_formas_pago($forma_pago);
                    if ($validation->pass == false) {
                        return $validation;
                    }
                }
            }
            $toReturn = new stdClass();
            $toReturn->pass = true;
            $toReturn->message = 'ok';
            return $toReturn;
        }
        catch(Exception $e) {
            $toReturn = new stdClass();
            $toReturn->pass = false;
            $toReturn->message = 'Error en la información remitida';
            return $toReturn;
        }
    }

    public function inserta_producto(Request $request, $pais) {
        $data = $request->json()->all();
        $factura_payload = FacturaPayload::where('IDCadena', $data['IDCadena'])->where('IDRestaurante', $data['IDRestaurante'])->where('IDFactura', $data['IDFactura'])->first();
        $detalle = $factura_payload->detalle;
        $modificadores = $factura_payload->modificadores;
        $new_producto = $data['producto'];
        $codModificadores = $data['codModificador'];
        $cantidad = $data['cantidad'];
        $detalleApp =  uniqid();
        $item = new stdClass();
        $item->detalleApp = $detalleApp;
        $item->codigoApp = $data['IDFactura'];
        $item->codPlu = $new_producto['codPlu'];
        $item->precioBruto = $new_producto['precioBruto'];
        $item->cantidad = $cantidad;
        array_push($detalle, $item);
        foreach($codModificadores as $codModificador) {
            $item_modificador = new stdClass();
            $item_modificador->detalleApp = $detalleApp;
            $item_modificador->codModificador = $codModificador;
            array_push($modificadores, $item_modificador);
        }
        try{
            DB::beginTransaction();
            $factura_payload->update([
                'detalle'=>$detalle,
                'modificadores'=>$modificadores,
            ]);
            DB::commit();
            return response()->json(["detalle"=>$detalle,"modificadores"=>$modificadores],200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(),400);
        }
    }


    public function inserta_varios_producto(Request $request, $pais) {
        $data = $request->json()->all();
        $factura_payload = FacturaPayload::where('IDCadena', $data['IDCadena'])->where('IDRestaurante', $data['IDRestaurante'])->where('IDFactura', $data['IDFactura'])->first();
        $detalle = $factura_payload->detalle;
        $modificadores = $factura_payload->modificadores;
        $items = $data['items'];
        foreach($items as $item) {
            $new_producto = $item['producto'];
            $codModificadores = $item['codModificador'];
            $cantidad = $item['cantidad'];
            $detalleApp =  uniqid();
            $new_item_detalle = new stdClass();
            $new_item_detalle->detalleApp = $detalleApp;
            $new_item_detalle->codigoApp = $data['IDFactura'];
            $new_item_detalle->codPlu = $new_producto['codPlu'];
            $new_item_detalle->precioBruto = $new_producto['precioBruto'];
            $new_item_detalle->cantidad = $cantidad;
            array_push($detalle, $new_item_detalle);
            foreach($codModificadores as $codModificador) {
                $new_item_modificador = new stdClass();
                $new_item_modificador->detalleApp = $detalleApp;
                $new_item_modificador->codModificador = $codModificador;
                array_push($modificadores, $new_item_modificador);
            }
        }
        try{
            DB::beginTransaction();
            $factura_payload->update([
                'detalle'=>$detalle,
                'modificadores'=>$modificadores,
            ]);
            DB::commit();
            return response()->json(["detalle"=>$detalle,"modificadores"=>$modificadores],200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(),400);
        }
    }

    public function borra_producto(Request $request, $pais) {
        $data = $request->json()->all();
        $factura_payload = FacturaPayload::where('IDCadena', $data['IDCadena'])->where('IDRestaurante', $data['IDRestaurante'])->where('IDFactura', $data['IDFactura'])->first();
        $detalle = $factura_payload->detalle;
        $modificadores = $factura_payload->modificadores;
        $detalleApp = $data['detalleApp'];
        $new_detalle = [];
        $new_modificadores = [];
        $eliminado = false;
        foreach($detalle as $item) {
            $detalle_item = (object) $item;
            if ($detalle_item->detalleApp == $detalleApp) {
                $eliminado = true;
            } else {
                array_push($new_detalle, $item);
            }
        }
        foreach($modificadores as $item) {
            $modificador_item = (object) $item;
            if ($modificador_item->detalleApp == $detalleApp) {
                $eliminado = true;
            } else {
                array_push($new_modificadores, $item);
            }
        }
        if ($eliminado) {
            try{
                DB::beginTransaction();
                $factura_payload->update([
                    'detalle'=>$new_detalle,
                    'modificadores'=>$new_modificadores,
                ]);
                DB::commit();
                return response()->json(["detalle"=>$new_detalle,"modificadores"=>$new_modificadores],200);
            } catch (Exception $e) {
                return response()->json($e->getMessage(),400);
            }
        } else {
            return response()->json("producto no encontrado", 400);
        }
    }

    public function borra_varios_producto(Request $request, $pais) {
        $data = $request->json()->all();
        $factura_payload = FacturaPayload::where('IDCadena', $data['IDCadena'])->where('IDRestaurante', $data['IDRestaurante'])->where('IDFactura', $data['IDFactura'])->first();
        $detalle = $factura_payload->detalle;
        $modificadores = $factura_payload->modificadores;
        $items = $data['items'];
        $new_modificadores = [];
        $new_detalle = [];
        $eliminados = false;

        foreach($detalle as $item) {
            $detalle_item = (object) $item;
            $eliminar_de_detalle = false;
            foreach($items as $item_borrar) {
                if ($detalle_item->detalleApp == $item_borrar) {
                    $eliminar_de_detalle = true;
                    $eliminados = true;
                }
            }
            if (!$eliminar_de_detalle) {
                array_push($new_detalle, $item);
            }
        }

        foreach($modificadores as $item) {
            $modificador_item = (object) $item;
            $eliminar_de_modificadores = false;
            foreach($items as $item_borrar) {
                if ($modificador_item->detalleApp == $item_borrar) {
                    $eliminar_de_modificadores = true;
                    $eliminados = true;
                }
            }
            if (!$eliminar_de_modificadores) {
                array_push($new_modificadores, $item);
            }
        }

        if (!$eliminados) {
            return response()->json("no se eliminaron productos", 400);
        } else {
            try{
                DB::beginTransaction();
                $factura_payload->update([
                    'detalle'=>$new_detalle,
                    'modificadores'=>$new_modificadores,
                ]);
                DB::commit();
                return response()->json(["detalle"=>$new_detalle,"modificadores"=>$new_modificadores],200);
            } catch (Exception $e) {
                return response()->json($e->getMessage(),400);
            }
        }
    }

    public function set_status(Request $request, $pais){
        try{
            DB::beginTransaction();
            $factura_payload = FacturaPayload::where('IDCadena', $request['IDCadena'])->where('IDRestaurante', $request['IDRestaurante'])->where('IDFactura', $request['IDFactura'])->update([
                'status'=>$request['status'],
            ]);
            DB::commit();
            return response()->json(true,200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(),400);
        }
    }

    public function busqueda_ultimo_pedido(Request $request, $pais){

        $identificacionCliente = $request['identificacionCliente'];
        $factura_payload = FacturaPayload::where('cabecera->identificacionCliente', $identificacionCliente)->first();


        return response()->json($factura_payload,200);

    }
}
