<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pesanan_model extends CI_Model {

    public function get_notifikasi_pesanan()
    {
        $pesanan = $this->db->get_where('pesanan', ['lunas' => 0])->result_array();
        $notif_pesanan = 0;
        foreach ($pesanan as $p) {
            $notif_pesanan += $p['quantity'];
        }
        return $notif_pesanan;
    }

    public function get_all_pesanan()
    {
        $this->db->select('pesanan.*, menu.nama as nama_pesanan, menu.gambar');
        $this->db->from('pesanan');
        $this->db->join('menu', 'menu.id = pesanan.menu_id');
        $this->db->where('pesanan.lunas', 0);
        return $this->db->get()->result_array();
    }

    public function get_total_bayar()
    {
        $this->db->select_sum('subtotal');
        $this->db->where('lunas', 0);
        $result = $this->db->get('pesanan')->row_array();
        return isset($result['subtotal']) ? $result['subtotal'] : 0;
    }

    public function update_pesanan($pesanan_id, $action)
    {
        $pesanan = $this->db->get_where('pesanan', ['id' => $pesanan_id])->row_array();

        if ($pesanan) {
            $quantity = ($action === '+') ? $pesanan['quantity'] + 1 : max(1, $pesanan['quantity'] - 1);
            $subtotal = $quantity * $pesanan['subtotal'] / $pesanan['quantity'];

            $data = [
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
            $this->db->where('id', $pesanan_id);
            $this->db->update('pesanan', $data);
        }
    }

    public function proses_pembayaran($no_pesanan)
    {
        $this->db->set('lunas', 1);
        $this->db->where('no_pesanan', $no_pesanan);
        $this->db->update('pesanan');
    }
}
