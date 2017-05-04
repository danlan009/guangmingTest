<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Vm extends Model
{
    //查询售货机点位信息
    public static function getVm($vmid){
        return \DB::table('vms')
            ->select('vms.vmid','vms.vm_name','nodes.address')
            ->join('nodes','vms.node_id','=','nodes.id')
            ->where('vms.vmid',$vmid)
            ->first();
    }
}
