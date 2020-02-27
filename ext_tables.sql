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
