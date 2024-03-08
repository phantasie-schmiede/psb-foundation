#
# Table structure for table 'tx_psbfoundation_accessed_language_labels'
#
CREATE TABLE tx_psbfoundation_accessed_language_labels
(
	hit_count int unsigned DEFAULT '0' NOT NULL,
	locallang_key varchar(255) DEFAULT '' NOT NULL
);

#
# Table structure for table 'tx_psbfoundation_missing_language_labels'
#
CREATE TABLE tx_psbfoundation_missing_language_labels
(
	locallang_key varchar(255) DEFAULT '' NOT NULL
);
