-- phpMyAdmin SQL Dump
-- version 2.6.4-pl3
-- http://www.phpmyadmin.net
-- 
-- Host: localhost:3306
-- Generatie Tijd: 24 Jun 2015 om 09:04
-- Server versie: 4.1.20
-- PHP Versie: 5.0.5
-- 
-- Database: `onetouch`
-- 

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `blokken`
-- 

CREATE TABLE `blokken` (
  `blokken_id` bigint(20) NOT NULL auto_increment,
  `is_aktief` enum('0','1') NOT NULL default '1',
  `ref_wedstrijd` bigint(20) NOT NULL default '0',
  `bloknr` int(11) NOT NULL default '0',
  `volgnr` varchar(16) NOT NULL default '0',
  `startnr` varchar(16) NOT NULL default '0',
  `exercise` int(11) NOT NULL default '0',
  UNIQUE KEY `blokken_id` (`blokken_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `club`
-- 

CREATE TABLE `club` (
  `club_id` bigint(20) NOT NULL auto_increment,
  `is_aktief` enum('0','1') NOT NULL default '1',
  `naam` varchar(64) NOT NULL default '',
  `plaats` varchar(48) NOT NULL default '',
  `land` varchar(32) NOT NULL default '',
  UNIQUE KEY `club_id` (`club_id`),
  KEY `naam` (`naam`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=482 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `gymnast`
-- 

CREATE TABLE `gymnast` (
  `gymnast_id` bigint(20) NOT NULL auto_increment,
  `is_aktief` enum('0','1') NOT NULL default '1',
  `naam` varchar(128) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `ref_club` bigint(32) NOT NULL default '0',
  `year_of_birth` year(4) NOT NULL default '0000',
  UNIQUE KEY `gymnast_id` (`gymnast_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=6182 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `level`
-- 

CREATE TABLE `level` (
  `level_id` bigint(20) NOT NULL auto_increment,
  `is_aktief` enum('0','1') NOT NULL default '1',
  `omschrijving` varchar(128) NOT NULL default '',
  `max_moeilijkheid` float NOT NULL default '0',
  UNIQUE KEY `level_id` (`level_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=289 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `participant`
-- 

CREATE TABLE `participant` (
  `participant_id` bigint(20) NOT NULL auto_increment,
  `is_aktief` enum('0','1') NOT NULL default '1',
  `ref_wedstrijd` bigint(20) NOT NULL default '0',
  `ref_gymnast` bigint(20) NOT NULL default '0',
  `ref_level` bigint(20) NOT NULL default '0',
  `toestel` varchar(32) NOT NULL default '',
  `blok` varchar(64) NOT NULL default '',
  `start_nr_1` int(11) NOT NULL default '0',
  `start_nr_2` int(11) NOT NULL default '0',
  `start_nr_3` int(11) NOT NULL default '0',
  `total_score_1` float NOT NULL default '0',
  `total_score_2` float NOT NULL default '0',
  `total_score_3` float NOT NULL default '0',
  `ranking_1` int(11) NOT NULL default '0',
  `ranking_2` int(11) NOT NULL default '0',
  `ranking_3` int(11) NOT NULL default '0',
  UNIQUE KEY `participant_id` (`participant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=9466 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `scores`
-- 

CREATE TABLE `scores` (
  `score_id` bigint(20) NOT NULL auto_increment,
  `is_aktief` enum('0','1') NOT NULL default '1',
  `session_id` varchar(64) NOT NULL default '',
  `upload_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ref_jury_nr` bigint(20) NOT NULL default '0',
  `jury_name` varchar(48) NOT NULL default '',
  `ref_gymnast` bigint(20) NOT NULL default '0',
  `gymnast_nr` bigint(20) NOT NULL default '0',
  `ref_wedstrijd_nr` bigint(20) NOT NULL default '0',
  `exercise` int(11) NOT NULL default '0',
  `score` float NOT NULL default '0',
  `deductions` text NOT NULL,
  `extra_deduction` float NOT NULL default '0',
  `jumps` int(2) NOT NULL default '0',
  `max_jump` int(2) NOT NULL default '0',
  UNIQUE KEY `score_id` (`score_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1064 ;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `wedstrijd`
-- 

CREATE TABLE `wedstrijd` (
  `wedstrijd_id` bigint(20) NOT NULL auto_increment,
  `naam` varchar(128) NOT NULL default '',
  `is_aktief` enum('0','1') NOT NULL default '1',
  `is_locked` enum('0','1') NOT NULL default '0',
  `password` varchar(32) NOT NULL default '',
  `jury_registered` set('1','2','3','4','5','6','7','8','9','10','11') NOT NULL default '',
  `jury_1` varchar(64) NOT NULL default '',
  `jury_1_last_uploaded` timestamp NULL default '0000-00-00 00:00:00',
  `jury_2` varchar(64) NOT NULL default '',
  `jury_2_last_uploaded` timestamp NULL default '0000-00-00 00:00:00',
  `jury_3` varchar(64) NOT NULL default '',
  `jury_3_last_uploaded` timestamp NULL default '0000-00-00 00:00:00',
  `jury_4` varchar(64) NOT NULL default '',
  `jury_4_last_uploaded` timestamp NULL default '0000-00-00 00:00:00',
  `jury_5` varchar(64) NOT NULL default '',
  `jury_5_last_uploaded` timestamp NULL default '0000-00-00 00:00:00',
  `jury_6` varchar(64) NOT NULL default '',
  `jury_6_last_uploaded` timestamp NULL default '0000-00-00 00:00:00',
  `jury_7` varchar(64) NOT NULL default '',
  `jury_7_last_uploaded` timestamp NULL default '0000-00-00 00:00:00',
  `jury_8` varchar(64) NOT NULL default '',
  `jury_8_last_uploaded` timestamp NULL default '0000-00-00 00:00:00',
  `jury_9` varchar(64) NOT NULL default '',
  `jury_9_last_uploaded` timestamp NULL default '0000-00-00 00:00:00',
  `jury_10` varchar(64) NOT NULL default '',
  `jury_10_last_uploaded` timestamp NULL default '0000-00-00 00:00:00',
  `jury_11` varchar(64) NOT NULL default '',
  `jury_11_last_uploaded` timestamp NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `wedstrijd_id` (`wedstrijd_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=74 ;
