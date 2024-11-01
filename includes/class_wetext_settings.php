<?php
class WP_WETEXT_Settings {
	
	protected $db;	
	protected $tb_prefix;
	public $flash = "disable";
	public $isflash = false;
	
	public function __construct() {
		global $wpdb, $table_prefix;		
		$this->db        = $wpdb;
		$this->tb_prefix = $table_prefix;
	}	

	/*/
	 * Get Wetext Plugin Settings
	/*/
	 
	public function wetext_get_settings() {
		$result = $this->db->get_row( "SELECT option_value as wetext_info FROM `{$this->tb_prefix}options` WHERE option_name='wetext_user_info'" );
		if ( $result ) {
			return $result;
		}
	}
	
	public function wetext_insert_settings( $data ) {			
		$result = $this->db->insert($this->tb_prefix . "options",
			array(				
				'option_name'   => 'wetext_user_info',
				'option_value'  => $data,
				'autoload'		=> 'no'						
			),
			array('%s','%s','%s')
		);		
		if ( $result ) {
			return true;
		}
	}
	
	public function wetext_update_settings( $data ) {		
		$result = $this->db->update( $this->tb_prefix . "options", array( 'option_value'  => $data ), array( "option_name" => "wetext_user_info") );		
		if ( $result ) {
			return true;
		}
	}	
	
	public function wetext_get_configuration() {
		$result = $this->db->get_row( "SELECT option_value as wetext_configuration FROM `{$this->tb_prefix}options` WHERE option_name='wetext_configuration_info'" );
		if ( $result ) {
			return $result;
		}
	}
	
	public function wetext_update_configuration($data) {		
		$fetchdata = $this->db->get_row( "SELECT option_value as wetext_configuration FROM `{$this->tb_prefix}options` WHERE option_name='wetext_configuration_info'" );		
		if(!empty($fetchdata)){			
			$result = $this->db->update( $this->tb_prefix . "options", array( 'option_value'  => $data ), array( "option_name" => "wetext_configuration_info") );					
		}else{			
			$result = $this->db->insert( $this->tb_prefix . "options", array( 'option_name' => 'wetext_configuration_info', 'option_value' => $data, 'autoload' => 'no'	), array( '%s','%s','%s' ) );				
		}
		
		if ( $result ) {
			return $result;
		}
			
	}	
	
	public function wetext_fetch_data( $field_name, $table_name, $where_clause='', $orderby='', $order='' ) {			
		$orderby = $orderby!='' ? ' ORDER BY '.$orderby : '';		
		if($where_clause!=''){
			$result = $this->db->get_row( "SELECT ". $field_name ." FROM {$this->tb_prefix}". $table_name ." WHERE ". $where_clause.' '.$orderby.' '.$order );
		}else{			
			$result = $this->db->get_row( "SELECT ". $field_name ." FROM {$this->tb_prefix}".$table_name.' '.$orderby.' '.$order );
		}
		
		if ( $result ) {
			return $result;
		}
	}
	
	public function wetext_fetch_dataset( $field_name, $table_name, $where_clause='', $order='', $orderby='', $limit='' ) {			
		$order = $order!='' ? ' ORDER BY '.$order : '';
		$limit = $limit!='' ? ' LIMIT '.$limit : '';		
		if($where_clause!=''){
			$result = $this->db->get_results( "SELECT ". $field_name ." FROM {$this->tb_prefix}". $table_name ." WHERE ". $where_clause.' '.$order.' '.$orderby.' '.$limit );
		}else{			
			$result = $this->db->get_results( "SELECT ". $field_name ." FROM {$this->tb_prefix}".$table_name.' '.$order.' '.$orderby.' '.$limit );
		}
		
		if ( $result ) {
			return $result;
		}
	}
	
	public function wetext_update_table( $table_name,$update_data,$where_clause,$where_value ) {		
		$update_result = $this->db->update( $this->tb_prefix . $table_name, $update_data , array( $where_clause => $where_value ) );		
		if ( $update_result ) {
			return true;
		}
	}
	
	public function wetext_insert_data( $table_name, $wetext_data ) {	
		$insert_result = $this->db->insert( $this->tb_prefix . $table_name, $wetext_data );		
		if ( $insert_result ) {
			return true;
		}
	}
}