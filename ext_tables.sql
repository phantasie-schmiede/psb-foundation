#
# Table structure for table 'tx_psbfoundation_service_doccomment_valueparser'
#
CREATE TABLE tx_psbfoundation_service_doccomment_valueparser (
    uid int(11) NOT NULL auto_increment,

    annotation_type varchar(255) DEFAULT '' NOT NULL,
    class_name varchar(255) DEFAULT '' NOT NULL,
    value_type varchar(255) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid)
);
