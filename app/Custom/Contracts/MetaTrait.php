<?php

namespace Vop\Custom\Contracts;

use Vop\Meta;

trait MetaTrait
{
    public function setMeta(array $meta_data, array $where = [])
    {
        if(!is_array(array_first($meta_data)))
            $meta_data = [$meta_data];

        if(empty($where)){
            foreach($meta_data as &$mData){
                $mData['meta_type'] = __CLASS__;
            }
            Meta::create($meta_data);
        } else {

            if(!isset($where['meta_type']))
                $where['meta_type'] = __CLASS__;

            foreach($meta_data as $meta) {
                $where['meta_key'] = $meta['meta_key'];
                Meta::where($where)->update($meta);
            }
        }
    }

    public function getMeta($meta_key = null)
    {
        $meta = Meta::where(['meta_type' => __CLASS__, 'meta_id' => $this->getKey()]);
        if(!is_null($meta_key)){
            $meta->where('meta_key', $meta_key);
        }
        return $meta->get();
    }
}