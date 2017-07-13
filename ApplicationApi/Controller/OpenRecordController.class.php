<?php
namespace Api\Controller;

class OpenRecordController extends CommonRestController {

    public function _filter(&$map) {
        if (session(C('ADMIN_AUTH_KEY'))) {

        } else {
            $map['company_id'] = session("user")["company_id"];
        }
    }

    public function lists() {
        parent::lists('OpenRecordView');
    }
}