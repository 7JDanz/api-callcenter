<?php

namespace App\Http\Controllers\Api\FacturaPayload;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FacturaPayload;
Use Exception;
use Illuminate\Support\Facades\DB;
use stdClass;

class FacturaPayloadController extends Controller
{
    public function get(Request $request, $pais) {
        $id = $request['IDFactura'];
        if ($id == null) {
           return response()->json(FacturaPayload::get(),200);
        } else {
           $factura_payload = FacturaPayload::where('IDFactura', $id)->first();
           if ($factura_payload) {
            return response()->json($factura_payload,200);
           } else {
            return response()->json("factura no encontrada",400);
           }
        }
    }

    public function post(Request $request, $pais) {
        $data = $request->json()->all();
        $new_factura_payload = new FacturaPayload();
        $new_factura_payload->orden = $data['orden'];
        $new_factura_payload->cabecera = $data['cabecera'];
        $new_factura_payload->valores = $data['valores'];
        $new_factura_payload->IDFactura = uniqid();
        $new_factura_payload->save();
        return response()->json($new_factura_payload,200);
    }

    public function put(Request $request, $pais) {
        try{
            DB::beginTransaction();
            $data = $request->json()->all();
            $factura_payload = FacturaPayload::where('IDFactura', $data['IDFactura'])->update([
               'orden'=>$data['orden'],
               'valores'=>$data['valores'],
               'cabecera'=>$data['cabecera'],
            ]);
            DB::commit();
            return response()->json($factura_payload,200);
         } catch (Exception $e) {
            return response()->json($e,400);
         }
    }

    public function put_cabecera(Request $request, $pais) {
        try{
            DB::beginTransaction();
            $data = $request->json()->all();
            $factura_payload = FacturaPayload::where('IDFactura', $data['IDFactura'])->update([
               'cabecera'=>$data['cabecera'],
            ]);
            DB::commit();
            return response()->json($factura_payload,200);
        } catch (Exception $e) {
            return response()->json($e,400);
        }
    }

    public function put_valores(Request $request, $pais) {
        try{
            DB::beginTransaction();
            $data = $request->json()->all();
            $factura_payload = FacturaPayload::where('IDFactura', $data['IDFactura'])->update([
               'valores'=>$data['valores'],
            ]);
            DB::commit();
            return response()->json($factura_payload,200);
        } catch (Exception $e) {
            return response()->json($e,400);
        }
    }

    public function put_orden(Request $request, $pais) {
        try{
            DB::beginTransaction();
            $data = $request->json()->all();
            $factura_payload = FacturaPayload::where('IDFactura', $data['IDFactura'])->update([
                'orden'=>$data['orden'],
            ]);
            DB::commit();
            return response()->json($factura_payload,200);
        } catch (Exception $e) {
            return response()->json($e,400);
        }
    }

    public function delete(Request $request, $pais) {
        $id = $request['id'];
        return FacturaPayload::destroy($id);
    }

    public function inserta_producto(Request $request, $pais) {
        $data = $request->json()->all();
        $factura_payload = FacturaPayload::where('IDFactura', $data['IDFactura'])->first();
        $orden = json_decode($factura_payload->orden);
        $new_producto = $data['producto'];
        $cantidad = $data['cantidad'];
        $item = new stdClass();
        $item->id = uniqid();
        $item->producto = $new_producto;
        $item->cantidad = $cantidad;
        array_push($orden, $item);
        try{
            DB::beginTransaction();
            $factura_payload->update([
                'orden'=>json_encode($orden),
            ]);
            DB::commit();
            return response()->json($orden,200);
        } catch (Exception $e) {
            return response()->json($e,400);
        }
    }


    public function inserta_varios_producto(Request $request, $pais) {
        $data = $request->json()->all();
        $factura_payload = FacturaPayload::where('IDFactura', $data['IDFactura'])->first();
        $orden = json_decode($factura_payload->orden);
        $new_productos = $data['productos'];
        foreach($new_productos as $new_producto) {
            $cantidad = $data['cantidad'];
            $item = new stdClass();
            $item->id = uniqid();
            $item->producto = $new_producto;
            $item->cantidad = $cantidad;
            array_push($orden, $item);
        }
        try{
            DB::beginTransaction();
            $factura_payload->update([
                'orden'=>json_encode($orden),
            ]);
            DB::commit();
            return response()->json($orden,200);
        } catch (Exception $e) {
            return response()->json($e,400);
        }
    }

    public function borra_producto(Request $request, $pais) {
        $data = $request->json()->all();
        $factura_payload = FacturaPayload::where('IDFactura', $data['IDFactura'])->first();
        $orden = json_decode($factura_payload->orden);
        $id_producto_borrar = $data['id'];
        $new_orden = [];
        $eliminado = false;
        foreach($orden as $item) {
            $detalle_item = (object) $item;
            if ($detalle_item->id == $id_producto_borrar) {
                $eliminado = true;
            } else {
                array_push($new_orden, $item);
            }
        }
        if ($eliminado) {
            try{
                DB::beginTransaction();
                $factura_payload->update([
                    'orden'=>json_encode($new_orden),
                ]);
                DB::commit();
                return response()->json($new_orden,200);
            } catch (Exception $e) {
                return response()->json($e,400);
            }
        } else {
            return response()->json("producto no encontrado", 400);
        }
    }

    public function borra_varios_producto(Request $request, $pais) {
        $data = $request->json()->all();
        $factura_payload = FacturaPayload::where('IDFactura', $data['IDFactura'])->first();
        $orden = json_decode($factura_payload->orden);
        $ids_producto_borrar = $data['ids'];
        $new_orden = [];
        $eliminados = false;
        foreach($ids_producto_borrar as $id_producto_borrar) {
            foreach($orden as $item) {
                $detalle_item = (object) $item;
                if ($detalle_item->id == $id_producto_borrar) {
                    $eliminados = true;
                } else {
                    array_push($new_orden, $item);
                }
            }
        }
        if (!$eliminados) {
            return response()->json("no se eliminaron productos", 400);
        } else {
            try{
                DB::beginTransaction();
                $factura_payload->update([
                    'orden'=>json_encode($new_orden),
                ]);
                DB::commit();
                return response()->json($new_orden,200);
            } catch (Exception $e) {
                return response()->json($e,400);
            }
        }
    }

    public function calcula_valores(Request $request, $pais) {
        $data = $request->json()->all();
        $factura_payload = FacturaPayload::where('IDFactura', $data['IDFactura'])->first();
        $costo_subtotal = 19; //aqui calcular el costo total como la suma de todo lo que agregue costo.
        $cabecera = [
            "SUBTOTAL"=>$costo_subtotal,
        ];
        $costos_insertar = $data['costos_insertar'];
        foreach($costos_insertar as $new_costo) {
            if ($new_costo["tipo"]=="calculo") {
                $new_valor = $costo_subtotal * $new_costo["factor"];
                array_push($cabecera, [$new_costo['etiqueta']=>$new_valor]);
            } else {
                array_push($cabecera, [$new_costo['etiqueta']=>$new_costo['valor']]);
            }
        }
        $costo_total = 0;
        foreach($cabecera as $cabecera_key=>$cabecera_value) {
            $costo_total += $cabecera_value;
        }
        array_push($cabecera, ["TOTAL"=>$costo_total]);
        try{
            DB::beginTransaction();
            $data = $request->json()->all();
            $response = $factura_payload->update([
               'cabecera'=>$cabecera,
            ]);
            DB::commit();
            return response()->json($response,200);
         } catch (Exception $e) {
            return response()->json($e,400);
         }
    }
}
