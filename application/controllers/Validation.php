<?php
/** @noinspection DuplicatedCode */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property User_model $user_model
 */
class Validation extends CI_Controller
{
	public function index()
	{
		$this->load->model('user_model');
		$valid_fields = array('transactionId', 'transactionSource', 'nik', 'threshold', 'image',
			'template', 'type', 'position', 'customer_id', 'user_id', 'password', 'ip');

		if ($this->input->method() == "post")
		{
			//Ambil input dari $_POST
			$post = json_decode($this->input->raw_input_stream, true);

			//Memeriksa seluruh input harus memiliki key yang cocok dengan valid_fields
			//Jika ada input key yang tidak cocok, return Error Parameter Tidak Sesuai
			if (!is_valid_fields($post, $valid_fields)) {
				json_response_error(
					6019,
					"Parameter Tidak Sesuai",
					$post
				);
			}

			//Memeriksa fields yang harus/wajib diisi,
			//jika ada yang kosong, return Error Parameter Tidak Sesuai
			$required_fields = array('image', 'nik', 'user_id', 'password', 'ip', 'threshold');
			if (!required_has_values($post, $required_fields))
			{
				json_response_error(
					6019,
					"Parameter Tidak Sesuai",
					$post
				);
			}

			//ubah/decode nik dari request menjadi, text aslinya (non-base64)
			$post_nik = base64_decode($post['nik']);

			//Ambil data user dari database, dgn parameter user_id atau nik
			$user = $this->user_model->get_user_by_nik($post_nik);
			if (!$user) {
				json_response_error(6019, "NIK Tidak Ditemukan", $post);
			}

			//Validasi user_id dan password

			//ubah user_id dari request, di-decode menjadi non-base64
			$post_user_id = base64_decode($post['user_id']);

			//ubah password dari request, di-decode menjadi non-base64
			$post_password = md5(base64_decode($post['password']));

			//compare/bandingkan user_id dan password dari request, dengan
			//user_id dan password yang tercatat di database
			if (!($post_user_id == $user->user_id && $post_password == $user->password))
			{
				json_response_error(5001, "Akun tidak ditemukan", $post);
			}

			//Maksimal limit berdasarkan jam (Jam 5 sore)
			/*$now = current_datetime('H');
			if (intval($now) > 17)
			{
				json_response_error(5002, "Kuota Hari Ini Habis, Silahkan Dicoba Esok Hari.", $post);
			}*/

			//Validasi IP
			$ip_address = ($post['ip'] == '127.0.0.1' || $post['ip'] == '::1') ? "localhost" : $post['ip'];
			$user_ip_address = ($user->ip == '127.0.0.1' || $user->ip == '::1') ? "localhost" : $user->ip;
			if ($ip_address != $user_ip_address)
			{
				json_response_error(5003, "IP Address Tidak Sesuai.", $post);
			}

			//Validasi Threshold (1 - 20 berdasarkan API Spec DUKCAPIL)
			$threshold = intval($post['threshold']);
			if ($threshold < 1 || $threshold > 20)
			{
				json_response_error(5003, "Threshold Harus Diantara 1-20.", $post);
			}

			//Validasi Image (Face Validation)
			$filename = $user->nik . ".jpg";
			$original_face_file = "public/images/{$filename}";
			try {
				//ambil base64 image dari post request
				$input_face = file_get_contents($post['image']);

				//ubah base64 menjadi file, lalu simpan ke temporary folder
				$tmppath = "public/tmp/";
				$compare_face_file = $tmppath . $filename;

				file_put_contents($compare_face_file, $input_face);

				//check mime type nya, harus jpg/jpeg
				$mimetype = mime_content_type($compare_face_file);
				$valid_mime_types = array('image/jpg', 'image/jpeg');

				//jika bukan jpg/jpeg, maka
				if (!in_array($mimetype, $valid_mime_types))
				{
					//hapus file dari folder temporary
					unlink($compare_face_file);

					//lalu kirimkan response error
					json_response_error(6015, "Foto tidak dapat diproses", $post);
				}

				//jika mime type jpg/jpeg, maka
				//$rootDir = realpath($_SERVER["DOCUMENT_ROOT"]);
				$rootDir = $_SERVER['DOCUMENT_ROOT'];
				$original_face = $rootDir  . "/dukcapil/{$original_face_file}";
				$compare_face = $rootDir  . "/dukcapil/{$compare_face_file}";

				//echo $original_face;
				//exit;

				$probability = compare($original_face, $compare_face);
				$compare_result = json_decode($probability, true);
				if (!empty($compare_result) && is_array($compare_result) && count($compare_result) > 0)
				{
					//hapus file compare yang disimpan di tmp folder
					if (file_exists($compare_face_file)) unlink($compare_face_file);

					$match = 0;
					if (isset($compare_result['matches'])) {
						$match = intval($compare_result['matches']);
					} elseif (isset($compare_result['match'])) {
						$match = intval($compare_result['match']);
					}

					if ($match == 1) {
						json_response_success($post);
					} else {
						json_response_error(6019, "Foto Tidak Sesuai.", $post);
					}
				}

			} catch (Exception $e) {
				//jika terjadi error saat pembuatan file, maka
				//tampilkan error response
				json_response_error(6015, "Foto tidak dapat diproses", $post);
			}
		}
	}

}
