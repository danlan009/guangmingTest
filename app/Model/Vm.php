<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class Vm extends Model
{
    protected $table = 'vms';
    //查询售货机点位信息
    public static function getVm($vmid){
        return DB::table('vms')
            ->select('vms.vmid','vms.vm_name','nodes.address')
            ->join('nodes','vms.node_id','=','nodes.id')
            ->where('vms.vmid',$vmid)
            ->first();
    }
}
