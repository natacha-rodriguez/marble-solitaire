
--
-- Database: `peg_solitaire_solutions`
--

-- --------------------------------------------------------

--
-- Table structure for table `solution_steps`
--

CREATE TABLE IF NOT EXISTS `solution_steps` (
  `solution_id` int(11) NOT NULL,
  `solution_step` int(11) NOT NULL,
  `from_x` tinyint(4) NOT NULL,
  `from_y` tinyint(4) NOT NULL,
  `to_x` tinyint(4) NOT NULL,
  `to_y` tinyint(4) NOT NULL,
  PRIMARY KEY (`solution_id`,`solution_step`),
  KEY `solution_id` (`solution_id`,`solution_step`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

