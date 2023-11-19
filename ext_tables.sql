CREATE TABLE tx_dmflocalcrm_domain_model_data_userdata (
  user_id varchar(60) DEFAULT '' NOT NULL,
  serialized_data mediumtext DEFAULT '',
  changed int(11) unsigned DEFAULT '0' NOT NULL,
  created int(11) unsigned DEFAULT '0' NOT NULL,

  UNIQUE unique_user_id (user_id)
);
