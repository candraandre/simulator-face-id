<?php

class User_model extends CI_Model
{

	function is_user_exists($nik, $user_id)
	{
		$query = $this->db->select()->where('nik', $nik)
			->or_where('user_id', $user_id)
			->get('user');

		$result = $query->result();
		return count($result) > 0 ? $result : false;
	}

	function insert_user(array $data)
	{
		if ($this->db->insert("user", $data))
		{
			return $data;
		}
		return false;
	}

	function get_user_by_nik($nik)
	{
		$query = $this->db->select()->where('nik', $nik)
			->get('user');

		$result = $query->result();
		return count($result) > 0 ? $result[0] : false;
	}


}
