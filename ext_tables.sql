#
# Table structure for table 'tx_psbfoundation_extension_information_mapping'
#
CREATE TABLE tx_psbfoundation_extension_information_mapping (
    class_name varchar(255) DEFAULT '' NOT NULL,
    extension_key varchar(255) DEFAULT '' NOT NULL,

    PRIMARY KEY (extension_key)
);

#
# Table structure for table 'tx_psbfoundation_missing_translations'
#
CREATE TABLE tx_psbfoundation_missing_translations (
    locallang_key varchar(255) DEFAULT '' NOT NULL,

    PRIMARY KEY (locallang_key)
);

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