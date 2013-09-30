-- phpMyAdmin SQL Dump
-- version 3.4.4
-- http://www.phpmyadmin.net
--
-- Host: zmp.nazwa.pl:3305
-- Czas wygenerowania: 04 Sie 2013, 14:34
-- Wersja serwera: 5.0.91
-- Wersja PHP: 5.2.14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Baza danych: `zmp_4`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `admin`
--

CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int(11) NOT NULL auto_increment,
  `admin_groups_id` int(11) default NULL,
  `admin_firstname` varchar(32) NOT NULL,
  `admin_lastname` varchar(32) default NULL,
  `admin_email_address` varchar(96) NOT NULL,
  `admin_password` varchar(40) NOT NULL,
  `admin_created` datetime default NULL,
  `admin_modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `admin_logdate` datetime default NULL,
  `admin_lognum` int(11) NOT NULL default '0',
  PRIMARY KEY  (`admin_id`),
  UNIQUE KEY `admin_email_address` (`admin_email_address`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin2 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `admin_files`
--

CREATE TABLE IF NOT EXISTS `admin_files` (
  `admin_files_id` int(11) NOT NULL auto_increment,
  `admin_files_name` varchar(64) NOT NULL,
  `admin_files_is_boxes` tinyint(5) NOT NULL default '0',
  `admin_files_to_boxes` int(11) NOT NULL default '0',
  `admin_groups_id` set('1','2') NOT NULL default '1',
  PRIMARY KEY  (`admin_files_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin2 AUTO_INCREMENT=49 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `admin_groups`
--

CREATE TABLE IF NOT EXISTS `admin_groups` (
  `admin_groups_id` int(11) NOT NULL auto_increment,
  `admin_groups_name` varchar(64) default NULL,
  PRIMARY KEY  (`admin_groups_id`),
  UNIQUE KEY `admin_groups_name` (`admin_groups_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin2 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `alogin` char(30) default NULL,
  `ahaslo` char(50) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin2 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `pokoje`
--

CREATE TABLE IF NOT EXISTS `pokoje` (
  `id` int(11) NOT NULL auto_increment,
  `space` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `description` text NOT NULL,
  `floor` int(11) NOT NULL,
  `price_model` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin2 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `rezerwacje`
--

CREATE TABLE IF NOT EXISTS `rezerwacje` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `imie` text,
  `nazwisko` text,
  `od` date default NULL,
  `do` date default NULL,
  `mail` char(255) default NULL,
  `telefon` char(255) default NULL,
  `uwagi` text,
  `ulica` varchar(255) NOT NULL,
  `numer` varchar(255) NOT NULL,
  `miasto` varchar(255) NOT NULL,
  `kod` varchar(6) NOT NULL,
  `opcje` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin2 AUTO_INCREMENT=91 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `terminy`
--

CREATE TABLE IF NOT EXISTS `terminy` (
  `id` int(11) NOT NULL auto_increment,
  `id_reservation` int(11) NOT NULL,
  `id_pokoje` int(11) NOT NULL,
  `data_przyjazdu` date NOT NULL,
  `data_wyjazdu` date NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=32 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `promotions`
--

CREATE TABLE `promotions` (
  `promotion_id` int(11) NOT NULL auto_increment,
  `days` int(3) NOT NULL,
  `percent` decimal(3,0) NOT NULL,
  PRIMARY KEY  (`promotion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin2 AUTO_INCREMENT=1 ;
