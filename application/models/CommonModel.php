<?php

/* MIHIR */

class CommonModel extends CI_Model {

    public function add($tbl = NULL, $record = NULL) {
        if (!empty($tbl) && !empty($record)) {
            $this->db->insert($tbl, $record);
            return $this->db->insert_id();
        } else {
            return FALSE;
        }
    }

    public function multiple_add($tbl = NULL, $records = NULL) {
        if (!empty($tbl) && !empty($records)) {
            $res = $this->db->insert_batch($tbl, $records);
            return $res;
        } else {
            return FALSE;
        }
    }

    public function edit($tbl = NULL, $record = NULL, $cond = NULL, $doNotEscape = FALSE) {
        if (!empty($tbl) && !empty($record) && !empty($cond)) {
            if ($doNotEscape) {
                foreach ($record as $key => $value) {
                    $this->db->set($key, $value, FALSE);
                }

                $this->db->where($cond);
                $res = $this->db->update($tbl);
            } else {
                $res = $this->db->update($tbl, $record, $cond);
            }

            return $res;
        } else {
            return FALSE;
        }
    }

    public function multiple_edit($tbl = NULL, $record = NULL, $col = NULL) {
        if (!empty($tbl) && !empty($record)) {
            $res = $this->db->update_batch($tbl, $record, $col);
            return $res;
        } else {
            return FALSE;
        }
    }

    public function delete($tbl = NULL, $cond = NULL) {
        if (!empty($tbl) && !empty($cond)) {
            $this->db->where($cond);
            $res = $this->db->delete($tbl);
            return $res;
        } else {
            return FALSE;
        }
    }

    public function multiple_delete($tbl = NULL, $column = NULL, $value = NULL) {
        if (!empty($tbl) && !empty($column) && !empty($value)) {
            $this->db->where_in($column, $value);
            $res = $this->db->delete($tbl);
            return $res;
        } else {
            return FALSE;
        }
    }

    public function getRow($tbl = NULL, $cond = NULL, $cols = NULL) {
        if (!empty($tbl)) {
            if (!empty($cols)) {
                $this->db->select($cols);
            }
            if (!empty($cond)) {
                $res = $this->db->get_where($tbl, $cond)->row_array();
            } else {
                $res = $this->db->get($tbl)->row_array();
            }
            return $res;
        } else {
            return FALSE;
        }
    }

    public function getRecords($tbl = NULL, $cond = NULL, $cols = NULL, $limit = NULL, $order_col = NULL, $order_by = NULL, $group_by = NULL) {
        if (!empty($tbl)) {
            if (!empty($cols)) {
                $this->db->select($cols);
            }
            if (!empty($limit)) {
                $this->db->limit($limit);
            }
            if (!empty($order_col) && !empty($order_by)) {
                $this->db->order_by($order_col, $order_by);
            }
            if (!empty($cond)) {
                $this->db->where($cond);
            }
            if (!empty($group_by)) {
                $this->db->group_by($group_by);
            }
            $res = $this->db->get($tbl)->result_array();
            return $res;
        } else {
            return FALSE;
        }
    }

    public function get_records_by_offset($tbl = NULL, $cond = NULL, $cols = NULL, $limit = NULL, $offset = NULL, $order_col = NULL, $order_by = NULL, $group_by = NULL, $demo = NULL) {
        if ($demo == 1) {
            echo $this->db->last_query();
            exit;
        }

        if (!empty($tbl)) {
            if (!empty($cols)) {
                $this->db->select($cols);
            }
            if (!empty($limit)) {
                $this->db->limit($limit, $offset);
            }
            if (!empty($order_col) && !empty($order_by)) {
                $this->db->order_by($order_col, $order_by);
            }
            if (!empty($cond)) {
                $this->db->where($cond);
            }
            if (!empty($group_by)) {
                $this->db->group_by($group_by);
            }
            $res = $this->db->get($tbl)->result_array();
            return $res;
        } else {
            return FALSE;
        }
    }

    public function get_records_in($tbl = NULL, $cols = NULL, $column = NULL, $value = NULL) {
        if (!empty($tbl)) {
            if (!empty($cols)) {
                $this->db->select($cols);
            }
            if (!empty($column) && !empty($value)) {
                $this->db->where_in($column, $value);
            }
            $res = $this->db->get($tbl)->result_array();
            return $res;
        } else {
            return FALSE;
        }
    }

    public function get_records_in_where($tbl = NULL, $cols = NULL, $cond = NULL, $column = NULL, $value = NULL) {
        if (!empty($tbl)) {
            if (!empty($cols)) {
                $this->db->select($cols);
            }
            if (!empty($cond)) {
                $this->db->where($cond);
            }
            if (!empty($column) && !empty($value)) {
                $this->db->where_in($column, $value);
            }
            $res = $this->db->get($tbl)->result_array();
            return $res;
//            echo $this->db->last_query();
//            exit;
        } else {
            return FALSE;
        }
    }

    public function search_by_like($tbl = NULL, $column = NULL, $match = NULL, $cols = NULL, $cond = NULL, $limit = NULL) {
        if (!empty($tbl)) {
            if (!empty($cols)) {
                $this->db->select($cols);
            }
            if (!empty($limit)) {
                $this->db->limit($limit);
            }
            if (!empty($column) && !empty($match)) {
                $this->db->like($column, $match);
            }
            if (!empty($cond)) {
                $this->db->where($cond);
            }
            $res = $this->db->get($tbl)->result_array();
            return $res;
        } else {
            return FALSE;
        }
    }

    public function get_record_count($tbl = NULL, $cond = NULL) {
        if (!empty($cond)) {
            $this->db->where($cond);
        }
        $res = $this->db->count_all_results($tbl);
        return $res;
    }

    /* public function copy_column_value($tbl = NULL, $copy_from = NULL, $copy_to = NULL, $cond = NULL)
      {
      if(!empty($tbl) && !empty($copy_from) && !empty($copy_to) && !empty($cond))
      {
      $this->db->where('emp_no', $data['id']);
      $res = $this->db->update($tbl, $data['title']);
      return $res;
      }
      } */

    public function get_last_record($tbl = NULL, $cols = NULL) {
        if (!empty($tbl)) {
            if (!empty($cols)) {
                $this->db->select($cols);
            } else {
                $this->db->select('*');
            }
            $this->db->from($tbl);
            $this->db->order_by('id', 'DESC');
            $this->db->limit('1');
            $get = $this->db->get();
            return $get->row_array();
        }
    }

}

?>