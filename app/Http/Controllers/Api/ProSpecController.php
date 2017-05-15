<?php
/**
 * Created by PhpStorm.
 * User: NYJ
 * Date: 2017/3/30
 * Time: 14:24
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\ErrorCode;
use App\Models\M3Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProSpecController extends Controller
{
    public function getProSpecList(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'product_id' => 'required|integer',
            ]);

        if ($validate->fails())
        {
            return M3Result::init(ErrorCode::$PARAM_ERROR,$validate->errors()->first());
        }

        $query = DB::table('pro_spec as ps');
            //->leftJoin('category as c', 'ps.category', '=', 'c.id');

        $nProductId = $request->input('product_id');
        $checkProExist = DB::table('product')->where('id',$nProductId)->first();
        if (is_null($checkProExist))
        {
            return M3Result::init(ErrorCode::$DATA_EMPTY);
        }

        if (!is_null($nProductId))
        {
            $query->where('ps.product_id','=',$nProductId);
        }

        if (!is_null($request->input('cur_price_start')))
        {
            $query->where('ps.cur_price','>=',$request->input('cur_price_start'));
        }

        if (!is_null($request->input('cur_price_end')))
        {
            $query->where('ps.cur_price','<',$request->input('cur_price_end'));
        }

        if (!is_null($request->input('market_price_start')))
        {
            $query->where('ps.market_price','>=',$request->input('market_price_start'));
        }

        if (!is_null($request->input('market_price_end')))
        {
            $query->where('ps.market_price','<',$request->input('market_price_end'));
        }


        if (!is_null($request->input('name')))
        {
            $query->where('ps.name','like','%'.$request->input('name').'%');
        }

        $query->select('ps.*');
        $productSpecList = $query->paginate(10);

        // $log = DB::getQueryLog();
        // dd($log);   //打印sql语句

        //$categoryList = $query->get();

        return M3Result::init(ErrorCode::$OK,$productSpecList);
    }

    public function add(Request $request)
    {
        $validate = Validator::make($request->all(),
        [
            // 'user_id'    => 'required|integer|',
            'product_id' => 'required|integer',
            'name'       => 'required|string',
            'price'      => 'required|numeric',
            'stock'      => 'required|integer'
        ]);

        if ($validate->fails())
        {
            return M3Result::init(ErrorCode::$PARAM_ERROR,$validate->errors()->first());
        }

        $chechProExist = DB::table('product')->where('id',$request->input('product_id'))->first();
        if (is_null($chechProExist))
        {
            return M3Result::init(ErrorCode::$DATA_EMPTY);

        }

        $newPro_specId = DB::table('pro_spec')->insertGetId(
            [
                'product_id' => $request->input('product_id'),
                'name'       => $request->input('name'),
                'price'      => $request->input('price'),
                'stock'      => $request->input('stock'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

        $newPro_spec = DB::table('pro_spec')->where('id', $newPro_specId)->first();

        return M3Result::init(ErrorCode::$OK,$newPro_spec);
    }

}