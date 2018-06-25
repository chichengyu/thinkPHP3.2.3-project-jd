<?php
namespace Admin\Model;
use Think\Model;
class MemberPriceModel extends Model 
{
	protected $insertFields = array('price','level_id','goods_id');
	protected $updateFields = array('id','price','level_id','goods_id');
	protected $_validate = array(
		array('price', 'require', '会员价格不能为空！', 1, 'regex', 3),
		array('price', 'currency', '会员价格必须是货币格式！', 1, 'regex', 3),
		array('level_id', 'require', '级别ID不能为空！', 1, 'regex', 3),
		array('level_id', 'number', '级别ID必须是一个整数！', 1, 'regex', 3),
		array('goods_id', 'require', '商品ID不能为空！', 1, 'regex', 3),
		array('goods_id', 'number', '商品ID必须是一个整数！', 1, 'regex', 3),
	);
	public function search($pageSize = 20)
	{
		/**************************************** 搜索 ****************************************/
		$where = array();
		$pricefrom = I('get.pricefrom');
		$priceto = I('get.priceto');
		if($pricefrom && $priceto)
			$where['price'] = array('between', array($pricefrom, $priceto));
		elseif($pricefrom)
			$where['price'] = array('egt', $pricefrom);
		elseif($priceto)
			$where['price'] = array('elt', $priceto);
		if($level_id = I('get.level_id'))
			$where['level_id'] = array('eq', $level_id);
		if($goods_id = I('get.goods_id'))
			$where['goods_id'] = array('eq', $goods_id);
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		/************************************** 取数据 ******************************************/
		$data['data'] = $this->alias('a')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		return $data;
	}
	// 添加前
	protected function _before_insert(&$data, $option)
	{
	}
	// 修改前
	protected function _before_update(&$data, $option)
	{
	}
	// 删除前
	protected function _before_delete($option)
	{
		if(is_array($option['where']['id']))
		{
			$this->error = '不支持批量删除';
			return FALSE;
		}
	}
	/************************************ 其他方法 ********************************************/
}