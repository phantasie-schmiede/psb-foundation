#
# Table structure for table 'cache_psbfoundation'
#
CREATE TABLE cache_psbfoundation
(
	checksum   varchar(255) DEFAULT '' NOT NULL,
	content    text                    NOT NULL,
	identifier varchar(255) DEFAULT '' NOT NULL
);

#
# Table structure for table 'cache_psbfoundation_tags'
#
CREATE TABLE cache_psbfoundation_tags
(
	identifier varchar(255) DEFAULT '' NOT NULL,
	tag        varchar(255) DEFAULT '' NOT NULL
);

#
# Table structure for table 'tx_psbfoundation_missing_language_labels'
#
CREATE TABLE tx_psbfoundation_missing_language_labels
(
	locallang_key varchar(255) DEFAULT '' NOT NULL
);
