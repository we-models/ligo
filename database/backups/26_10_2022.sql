-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2022 at 03:27 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `opcion2`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `businesses`
--

CREATE TABLE `businesses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `businesses`
--

INSERT INTO `businesses` (`id`, `code`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'b6b6bbe6-d104-4d5c-ae1b-f7cb7999b072', 'La esquina de Ales', '<p>Asadero de pollo</p>', '2022-10-25 08:13:03', '2022-10-25 08:13:03', NULL),
(2, 'd0e5e6f9-c806-4253-8039-7f5f282f0a31', 'La tablita del tartaro', '<p>Venta de comida asada</p>', '2022-10-25 08:13:25', '2022-10-25 08:13:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(10) UNSIGNED NOT NULL,
  `iso` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nicename` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso3` char(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numcode` smallint(6) DEFAULT NULL,
  `phonecode` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `data_type`
--

CREATE TABLE `data_type` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `data_type`
--

INSERT INTO `data_type` (`id`, `name`) VALUES
(1, 'Text'),
(2, 'Integer'),
(3, 'Decimal'),
(4, 'Variable'),
(5, 'HTML'),
(6, 'Date'),
(7, 'Hour'),
(8, 'Datetime'),
(9, 'Color'),
(10, 'Bool'),
(11, 'Email'),
(12, 'Phone'),
(13, 'Image');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `business` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `icon`, `created_at`, `updated_at`, `deleted_at`, `business`) VALUES
(1, 'Home', 'fas fa-home', '2022-10-25 08:20:56', '2022-10-25 08:28:46', NULL, 1),
(2, 'Roles', 'fab fa-buromobelexperte', '2022-10-26 07:18:56', '2022-10-26 07:18:56', NULL, NULL),
(3, 'Permisos', 'fas fa-newspaper', '2022-10-26 07:19:14', '2022-10-26 07:19:14', NULL, NULL),
(4, 'Empresas', 'fas fa-building', '2022-10-26 07:20:43', '2022-10-26 07:20:43', NULL, NULL),
(5, 'Grupos', 'fas fa-object-group', '2022-10-26 07:20:56', '2022-10-26 07:20:56', NULL, NULL),
(6, 'Usuarios', 'fas fa-user', '2022-10-26 07:21:11', '2022-10-26 07:21:11', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `groups_roles`
--

CREATE TABLE `groups_roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role` bigint(20) UNSIGNED NOT NULL,
  `group` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `groups_roles`
--

INSERT INTO `groups_roles` (`id`, `role`, `group`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 1, '2022-10-25 08:30:36', '2022-10-25 08:30:36', NULL),
(2, 3, 1, '2022-10-25 08:30:37', '2022-10-25 08:30:37', NULL),
(3, 4, 1, '2022-10-25 08:30:38', '2022-10-25 08:30:38', NULL),
(4, 5, 1, '2022-10-25 08:30:39', '2022-10-25 08:30:39', NULL),
(5, 1, 1, '2022-10-25 08:30:40', '2022-10-25 08:30:40', NULL),
(6, 1, 2, '2022-10-26 07:48:00', '2022-10-26 07:48:00', NULL),
(7, 1, 3, '2022-10-26 07:48:02', '2022-10-26 07:48:02', NULL),
(8, 1, 4, '2022-10-26 07:48:04', '2022-10-26 07:48:04', NULL),
(9, 1, 5, '2022-10-26 07:48:05', '2022-10-26 07:48:05', NULL),
(10, 1, 6, '2022-10-26 07:48:06', '2022-10-26 07:48:06', NULL),
(11, 2, 2, '2022-10-26 07:48:08', '2022-10-26 07:48:08', NULL),
(12, 2, 3, '2022-10-26 07:48:09', '2022-10-26 07:48:09', NULL),
(13, 2, 4, '2022-10-26 07:48:10', '2022-10-26 07:48:10', NULL),
(14, 2, 5, '2022-10-26 07:48:11', '2022-10-26 07:48:11', NULL),
(15, 2, 6, '2022-10-26 07:48:12', '2022-10-26 07:48:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `icons`
--

CREATE TABLE `icons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `icons`
--

INSERT INTO `icons` (`id`, `name`) VALUES
(659, 'fab fa-500px'),
(660, 'fab fa-accessible-icon'),
(661, 'fab fa-accusoft'),
(662, 'fab fa-adn'),
(663, 'fab fa-adversal'),
(664, 'fab fa-affiliatetheme'),
(665, 'fab fa-algolia'),
(666, 'fab fa-amazon'),
(667, 'fab fa-amazon-pay'),
(668, 'fab fa-amilia'),
(669, 'fab fa-android'),
(670, 'fab fa-angellist'),
(671, 'fab fa-angrycreative'),
(672, 'fab fa-angular'),
(673, 'fab fa-app-store'),
(674, 'fab fa-app-store-ios'),
(675, 'fab fa-apper'),
(676, 'fab fa-apple'),
(677, 'fab fa-apple-pay'),
(678, 'fab fa-asymmetrik'),
(679, 'fab fa-audible'),
(680, 'fab fa-autoprefixer'),
(681, 'fab fa-avianex'),
(682, 'fab fa-aviato'),
(683, 'fab fa-aws'),
(684, 'fab fa-bandcamp'),
(685, 'fab fa-behance'),
(686, 'fab fa-behance-square'),
(687, 'fab fa-bimobject'),
(688, 'fab fa-bitbucket'),
(689, 'fab fa-bitcoin'),
(690, 'fab fa-bity'),
(691, 'fab fa-black-tie'),
(692, 'fab fa-blackberry'),
(693, 'fab fa-blogger'),
(694, 'fab fa-blogger-b'),
(695, 'fab fa-bluetooth'),
(696, 'fab fa-bluetooth-b'),
(697, 'fab fa-btc'),
(698, 'fab fa-buromobelexperte'),
(699, 'fab fa-buysellads'),
(700, 'fab fa-cc-amazon-pay'),
(701, 'fab fa-cc-amex'),
(702, 'fab fa-cc-apple-pay'),
(703, 'fab fa-cc-diners-club'),
(704, 'fab fa-cc-discover'),
(705, 'fab fa-cc-jcb'),
(706, 'fab fa-cc-mastercard'),
(707, 'fab fa-cc-paypal'),
(708, 'fab fa-cc-stripe'),
(709, 'fab fa-cc-visa'),
(710, 'fab fa-centercode'),
(711, 'fab fa-chrome'),
(712, 'fab fa-cloudscale'),
(713, 'fab fa-cloudsmith'),
(714, 'fab fa-cloudversify'),
(715, 'fab fa-codepen'),
(716, 'fab fa-codiepie'),
(717, 'fab fa-connectdevelop'),
(718, 'fab fa-contao'),
(719, 'fab fa-cpanel'),
(720, 'fab fa-creative-commons'),
(721, 'fab fa-css3'),
(722, 'fab fa-css3-alt'),
(723, 'fab fa-cuttlefish'),
(724, 'fab fa-d-and-d'),
(725, 'fab fa-dashcube'),
(726, 'fab fa-delicious'),
(727, 'fab fa-deploydog'),
(728, 'fab fa-deskpro'),
(729, 'fab fa-deviantart'),
(730, 'fab fa-digg'),
(731, 'fab fa-digital-ocean'),
(732, 'fab fa-discord'),
(733, 'fab fa-discourse'),
(734, 'fab fa-dochub'),
(735, 'fab fa-docker'),
(736, 'fab fa-draft2digital'),
(737, 'fab fa-dribbble'),
(738, 'fab fa-dribbble-square'),
(739, 'fab fa-dropbox'),
(740, 'fab fa-drupal'),
(741, 'fab fa-dyalog'),
(742, 'fab fa-earlybirds'),
(743, 'fab fa-edge'),
(744, 'fab fa-elementor'),
(745, 'fab fa-ember'),
(746, 'fab fa-empire'),
(747, 'fab fa-envira'),
(748, 'fab fa-erlang'),
(749, 'fab fa-ethereum'),
(750, 'fab fa-etsy'),
(751, 'fab fa-expeditedssl'),
(752, 'fab fa-facebook'),
(753, 'fab fa-facebook-f'),
(754, 'fab fa-facebook-messenger'),
(755, 'fab fa-facebook-square'),
(756, 'fab fa-firefox'),
(757, 'fab fa-first-order'),
(758, 'fab fa-firstdraft'),
(759, 'fab fa-flickr'),
(760, 'fab fa-flipboard'),
(761, 'fab fa-fly'),
(762, 'fab fa-font-awesome'),
(763, 'fab fa-font-awesome-alt'),
(764, 'fab fa-font-awesome-flag'),
(765, 'fab fa-fonticons'),
(766, 'fab fa-fonticons-fi'),
(767, 'fab fa-fort-awesome'),
(768, 'fab fa-fort-awesome-alt'),
(769, 'fab fa-forumbee'),
(770, 'fab fa-foursquare'),
(771, 'fab fa-free-code-camp'),
(772, 'fab fa-freebsd'),
(773, 'fab fa-get-pocket'),
(774, 'fab fa-gg'),
(775, 'fab fa-gg-circle'),
(776, 'fab fa-git'),
(777, 'fab fa-git-square'),
(778, 'fab fa-github'),
(779, 'fab fa-github-alt'),
(780, 'fab fa-github-square'),
(781, 'fab fa-gitkraken'),
(782, 'fab fa-gitlab'),
(783, 'fab fa-gitter'),
(784, 'fab fa-glide'),
(785, 'fab fa-glide-g'),
(786, 'fab fa-gofore'),
(787, 'fab fa-goodreads'),
(788, 'fab fa-goodreads-g'),
(789, 'fab fa-google'),
(790, 'fab fa-google-drive'),
(791, 'fab fa-google-play'),
(792, 'fab fa-google-plus'),
(793, 'fab fa-google-plus-g'),
(794, 'fab fa-google-plus-square'),
(795, 'fab fa-google-wallet'),
(796, 'fab fa-gratipay'),
(797, 'fab fa-grav'),
(798, 'fab fa-gripfire'),
(799, 'fab fa-grunt'),
(800, 'fab fa-gulp'),
(801, 'fab fa-hacker-news'),
(802, 'fab fa-hacker-news-square'),
(803, 'fab fa-hips'),
(804, 'fab fa-hire-a-helper'),
(805, 'fab fa-hooli'),
(806, 'fab fa-hotjar'),
(807, 'fab fa-houzz'),
(808, 'fab fa-html5'),
(809, 'fab fa-hubspot'),
(810, 'fab fa-imdb'),
(811, 'fab fa-instagram'),
(812, 'fab fa-internet-explorer'),
(813, 'fab fa-ioxhost'),
(814, 'fab fa-itunes'),
(815, 'fab fa-itunes-note'),
(816, 'fab fa-jenkins'),
(817, 'fab fa-joget'),
(818, 'fab fa-joomla'),
(819, 'fab fa-js'),
(820, 'fab fa-js-square'),
(821, 'fab fa-jsfiddle'),
(822, 'fab fa-keycdn'),
(823, 'fab fa-kickstarter'),
(824, 'fab fa-kickstarter-k'),
(825, 'fab fa-korvue'),
(826, 'fab fa-laravel'),
(827, 'fab fa-lastfm'),
(828, 'fab fa-lastfm-square'),
(829, 'fab fa-leanpub'),
(830, 'fab fa-less'),
(831, 'fab fa-line'),
(832, 'fab fa-linkedin'),
(833, 'fab fa-linkedin-in'),
(834, 'fab fa-linode'),
(835, 'fab fa-linux'),
(836, 'fab fa-lyft'),
(837, 'fab fa-magento'),
(838, 'fab fa-maxcdn'),
(839, 'fab fa-medapps'),
(840, 'fab fa-medium'),
(841, 'fab fa-medium-m'),
(842, 'fab fa-medrt'),
(843, 'fab fa-meetup'),
(844, 'fab fa-microsoft'),
(845, 'fab fa-mix'),
(846, 'fab fa-mixcloud'),
(847, 'fab fa-mizuni'),
(848, 'fab fa-modx'),
(849, 'fab fa-monero'),
(850, 'fab fa-napster'),
(851, 'fab fa-nintendo-switch'),
(852, 'fab fa-node'),
(853, 'fab fa-node-js'),
(854, 'fab fa-npm'),
(855, 'fab fa-ns8'),
(856, 'fab fa-nutritionix'),
(857, 'fab fa-odnoklassniki'),
(858, 'fab fa-odnoklassniki-square'),
(859, 'fab fa-opencart'),
(860, 'fab fa-openid'),
(861, 'fab fa-opera'),
(862, 'fab fa-optin-monster'),
(863, 'fab fa-osi'),
(864, 'fab fa-page4'),
(865, 'fab fa-pagelines'),
(866, 'fab fa-palfed'),
(867, 'fab fa-patreon'),
(868, 'fab fa-paypal'),
(869, 'fab fa-periscope'),
(870, 'fab fa-phabricator'),
(871, 'fab fa-phoenix-framework'),
(872, 'fab fa-php'),
(873, 'fab fa-pied-piper'),
(874, 'fab fa-pied-piper-alt'),
(875, 'fab fa-pied-piper-pp'),
(876, 'fab fa-pinterest'),
(877, 'fab fa-pinterest-p'),
(878, 'fab fa-pinterest-square'),
(879, 'fab fa-playstation'),
(880, 'fab fa-product-hunt'),
(881, 'fab fa-pushed'),
(882, 'fab fa-python'),
(883, 'fab fa-qq'),
(884, 'fab fa-quinscape'),
(885, 'fab fa-quora'),
(886, 'fab fa-ravelry'),
(887, 'fab fa-react'),
(888, 'fab fa-readme'),
(889, 'fab fa-rebel'),
(890, 'fab fa-red-river'),
(891, 'fab fa-reddit'),
(892, 'fab fa-reddit-alien'),
(893, 'fab fa-reddit-square'),
(894, 'fab fa-rendact'),
(895, 'fab fa-renren'),
(896, 'fab fa-replyd'),
(897, 'fab fa-resolving'),
(898, 'fab fa-rocketchat'),
(899, 'fab fa-rockrms'),
(900, 'fab fa-safari'),
(901, 'fab fa-sass'),
(902, 'fab fa-schlix'),
(903, 'fab fa-scribd'),
(904, 'fab fa-searchengin'),
(905, 'fab fa-sellcast'),
(906, 'fab fa-sellsy'),
(907, 'fab fa-servicestack'),
(908, 'fab fa-shirtsinbulk'),
(909, 'fab fa-simplybuilt'),
(910, 'fab fa-sistrix'),
(911, 'fab fa-skyatlas'),
(912, 'fab fa-skype'),
(913, 'fab fa-slack'),
(914, 'fab fa-slack-hash'),
(915, 'fab fa-slideshare'),
(916, 'fab fa-snapchat'),
(917, 'fab fa-snapchat-ghost'),
(918, 'fab fa-snapchat-square'),
(919, 'fab fa-soundcloud'),
(920, 'fab fa-speakap'),
(921, 'fab fa-spotify'),
(922, 'fab fa-stack-exchange'),
(923, 'fab fa-stack-overflow'),
(924, 'fab fa-staylinked'),
(925, 'fab fa-steam'),
(926, 'fab fa-steam-square'),
(927, 'fab fa-steam-symbol'),
(928, 'fab fa-sticker-mule'),
(929, 'fab fa-strava'),
(930, 'fab fa-stripe'),
(931, 'fab fa-stripe-s'),
(932, 'fab fa-studiovinari'),
(933, 'fab fa-stumbleupon'),
(934, 'fab fa-stumbleupon-circle'),
(935, 'fab fa-superpowers'),
(936, 'fab fa-supple'),
(937, 'fab fa-telegram'),
(938, 'fab fa-telegram-plane'),
(939, 'fab fa-tencent-weibo'),
(940, 'fab fa-themeisle'),
(941, 'fab fa-trello'),
(942, 'fab fa-tripadvisor'),
(943, 'fab fa-tumblr'),
(944, 'fab fa-tumblr-square'),
(945, 'fab fa-twitch'),
(946, 'fab fa-twitter'),
(947, 'fab fa-twitter-square'),
(948, 'fab fa-typo3'),
(949, 'fab fa-uber'),
(950, 'fab fa-uikit'),
(951, 'fab fa-uniregistry'),
(952, 'fab fa-untappd'),
(953, 'fab fa-usb'),
(954, 'fab fa-ussunnah'),
(955, 'fab fa-vaadin'),
(956, 'fab fa-viacoin'),
(957, 'fab fa-viadeo'),
(958, 'fab fa-viadeo-square'),
(959, 'fab fa-viber'),
(960, 'fab fa-vimeo'),
(961, 'fab fa-vimeo-square'),
(962, 'fab fa-vimeo-v'),
(963, 'fab fa-vine'),
(964, 'fab fa-vk'),
(965, 'fab fa-vnv'),
(966, 'fab fa-vuejs'),
(967, 'fab fa-weibo'),
(968, 'fab fa-weixin'),
(969, 'fab fa-whatsapp'),
(970, 'fab fa-whatsapp-square'),
(971, 'fab fa-whmcs'),
(972, 'fab fa-wikipedia-w'),
(973, 'fab fa-windows'),
(974, 'fab fa-wordpress'),
(975, 'fab fa-wordpress-simple'),
(976, 'fab fa-wpbeginner'),
(977, 'fab fa-wpexplorer'),
(978, 'fab fa-wpforms'),
(979, 'fab fa-xbox'),
(980, 'fab fa-xing'),
(981, 'fab fa-xing-square'),
(982, 'fab fa-y-combinator'),
(983, 'fab fa-yahoo'),
(984, 'fab fa-yandex'),
(985, 'fab fa-yandex-international'),
(986, 'fab fa-yelp'),
(987, 'fab fa-yoast'),
(988, 'fab fa-youtube'),
(989, 'fab fa-youtube-square'),
(543, 'far fa-address-book'),
(544, 'far fa-address-card'),
(545, 'far fa-arrow-alt-circle-down'),
(546, 'far fa-arrow-alt-circle-left'),
(547, 'far fa-arrow-alt-circle-right'),
(548, 'far fa-arrow-alt-circle-up'),
(549, 'far fa-bell'),
(550, 'far fa-bell-slash'),
(551, 'far fa-bookmark'),
(552, 'far fa-building'),
(553, 'far fa-calendar'),
(554, 'far fa-calendar-alt'),
(555, 'far fa-calendar-check'),
(556, 'far fa-calendar-minus'),
(557, 'far fa-calendar-plus'),
(558, 'far fa-calendar-times'),
(559, 'far fa-caret-square-down'),
(560, 'far fa-caret-square-left'),
(561, 'far fa-caret-square-right'),
(562, 'far fa-caret-square-up'),
(563, 'far fa-chart-bar'),
(564, 'far fa-check-circle'),
(565, 'far fa-check-square'),
(566, 'far fa-circle'),
(567, 'far fa-clipboard'),
(568, 'far fa-clock'),
(569, 'far fa-clone'),
(570, 'far fa-closed-captioning'),
(571, 'far fa-comment'),
(572, 'far fa-comment-alt'),
(573, 'far fa-comments'),
(574, 'far fa-compass'),
(575, 'far fa-copy'),
(576, 'far fa-copyright'),
(577, 'far fa-credit-card'),
(578, 'far fa-dot-circle'),
(579, 'far fa-edit'),
(580, 'far fa-envelope'),
(581, 'far fa-envelope-open'),
(582, 'far fa-eye-slash'),
(583, 'far fa-file'),
(584, 'far fa-file-alt'),
(585, 'far fa-file-archive'),
(586, 'far fa-file-audio'),
(587, 'far fa-file-code'),
(588, 'far fa-file-excel'),
(589, 'far fa-file-image'),
(590, 'far fa-file-pdf'),
(591, 'far fa-file-powerpoint'),
(592, 'far fa-file-video'),
(593, 'far fa-file-word'),
(594, 'far fa-flag'),
(595, 'far fa-folder'),
(596, 'far fa-folder-open'),
(597, 'far fa-frown'),
(598, 'far fa-futbol'),
(599, 'far fa-gem'),
(600, 'far fa-hand-lizard'),
(601, 'far fa-hand-paper'),
(602, 'far fa-hand-peace'),
(603, 'far fa-hand-point-down'),
(604, 'far fa-hand-point-left'),
(605, 'far fa-hand-point-right'),
(606, 'far fa-hand-point-up'),
(607, 'far fa-hand-pointer'),
(608, 'far fa-hand-rock'),
(609, 'far fa-hand-scissors'),
(610, 'far fa-hand-spock'),
(611, 'far fa-handshake'),
(612, 'far fa-hdd'),
(613, 'far fa-heart'),
(614, 'far fa-hospital'),
(615, 'far fa-hourglass'),
(616, 'far fa-id-badge'),
(617, 'far fa-id-card'),
(618, 'far fa-image'),
(619, 'far fa-images'),
(620, 'far fa-keyboard'),
(621, 'far fa-lemon'),
(622, 'far fa-life-ring'),
(623, 'far fa-lightbulb'),
(624, 'far fa-list-alt'),
(625, 'far fa-map'),
(626, 'far fa-meh'),
(627, 'far fa-minus-square'),
(628, 'far fa-money-bill-alt'),
(629, 'far fa-moon'),
(630, 'far fa-newspaper'),
(631, 'far fa-object-group'),
(632, 'far fa-object-ungroup'),
(633, 'far fa-paper-plane'),
(634, 'far fa-pause-circle'),
(635, 'far fa-play-circle'),
(636, 'far fa-plus-square'),
(637, 'far fa-question-circle'),
(638, 'far fa-registered'),
(639, 'far fa-save'),
(640, 'far fa-share-square'),
(641, 'far fa-smile'),
(642, 'far fa-snowflake'),
(643, 'far fa-square'),
(644, 'far fa-star'),
(645, 'far fa-star-half'),
(646, 'far fa-sticky-note'),
(647, 'far fa-stop-circle'),
(648, 'far fa-sun'),
(649, 'far fa-thumbs-down'),
(650, 'far fa-thumbs-up'),
(651, 'far fa-times-circle'),
(652, 'far fa-trash-alt'),
(653, 'far fa-user'),
(654, 'far fa-user-circle'),
(655, 'far fa-window-close'),
(656, 'far fa-window-maximize'),
(657, 'far fa-window-minimize'),
(658, 'far fa-window-restore'),
(1, 'fas fa-address-book'),
(2, 'fas fa-address-card'),
(3, 'fas fa-adjust'),
(4, 'fas fa-align-center'),
(5, 'fas fa-align-justify'),
(6, 'fas fa-align-left'),
(7, 'fas fa-align-right'),
(8, 'fas fa-allergies'),
(9, 'fas fa-ambulance'),
(10, 'fas fa-american-sign-language-interpreting'),
(11, 'fas fa-anchor'),
(12, 'fas fa-angle-double-down'),
(13, 'fas fa-angle-double-left'),
(14, 'fas fa-angle-double-right'),
(15, 'fas fa-angle-double-up'),
(16, 'fas fa-angle-down'),
(17, 'fas fa-angle-left'),
(18, 'fas fa-angle-right'),
(19, 'fas fa-angle-up'),
(20, 'fas fa-archive'),
(21, 'fas fa-arrow-alt-circle-down'),
(22, 'fas fa-arrow-alt-circle-left'),
(23, 'fas fa-arrow-alt-circle-right'),
(24, 'fas fa-arrow-alt-circle-up'),
(25, 'fas fa-arrow-circle-down'),
(26, 'fas fa-arrow-circle-left'),
(27, 'fas fa-arrow-circle-right'),
(28, 'fas fa-arrow-circle-up'),
(29, 'fas fa-arrow-down'),
(30, 'fas fa-arrow-left'),
(31, 'fas fa-arrow-right'),
(32, 'fas fa-arrow-up'),
(33, 'fas fa-arrows-alt'),
(34, 'fas fa-arrows-alt-h'),
(35, 'fas fa-arrows-alt-v'),
(36, 'fas fa-assistive-listening-systems'),
(37, 'fas fa-asterisk'),
(38, 'fas fa-at'),
(39, 'fas fa-audio-description'),
(40, 'fas fa-backward'),
(41, 'fas fa-balance-scale'),
(42, 'fas fa-ban'),
(43, 'fas fa-band-aid'),
(44, 'fas fa-barcode'),
(45, 'fas fa-bars'),
(46, 'fas fa-baseball-ball'),
(47, 'fas fa-basketball-ball'),
(48, 'fas fa-bath'),
(49, 'fas fa-battery-empty'),
(50, 'fas fa-battery-full'),
(51, 'fas fa-battery-half'),
(52, 'fas fa-battery-quarter'),
(53, 'fas fa-battery-three-quarters'),
(54, 'fas fa-bed'),
(55, 'fas fa-beer'),
(56, 'fas fa-bell'),
(57, 'fas fa-bell-slash'),
(58, 'fas fa-bicycle'),
(59, 'fas fa-binoculars'),
(60, 'fas fa-birthday-cake'),
(61, 'fas fa-blind'),
(62, 'fas fa-bold'),
(63, 'fas fa-bolt'),
(64, 'fas fa-bomb'),
(65, 'fas fa-book'),
(66, 'fas fa-bookmark'),
(67, 'fas fa-bowling-ball'),
(68, 'fas fa-box'),
(69, 'fas fa-box-open'),
(70, 'fas fa-boxes'),
(71, 'fas fa-braille'),
(72, 'fas fa-briefcase'),
(73, 'fas fa-briefcase-medical'),
(74, 'fas fa-bug'),
(75, 'fas fa-building'),
(76, 'fas fa-bullhorn'),
(77, 'fas fa-bullseye'),
(78, 'fas fa-burn'),
(79, 'fas fa-bus'),
(80, 'fas fa-calculator'),
(81, 'fas fa-calendar'),
(82, 'fas fa-calendar-alt'),
(83, 'fas fa-calendar-check'),
(84, 'fas fa-calendar-minus'),
(85, 'fas fa-calendar-plus'),
(86, 'fas fa-calendar-times'),
(87, 'fas fa-camera'),
(88, 'fas fa-camera-retro'),
(89, 'fas fa-capsules'),
(90, 'fas fa-car'),
(91, 'fas fa-caret-down'),
(92, 'fas fa-caret-left'),
(93, 'fas fa-caret-right'),
(94, 'fas fa-caret-square-down'),
(95, 'fas fa-caret-square-left'),
(96, 'fas fa-caret-square-right'),
(97, 'fas fa-caret-square-up'),
(98, 'fas fa-caret-up'),
(99, 'fas fa-cart-arrow-down'),
(100, 'fas fa-cart-plus'),
(101, 'fas fa-certificate'),
(102, 'fas fa-chart-area'),
(103, 'fas fa-chart-bar'),
(104, 'fas fa-chart-line'),
(105, 'fas fa-chart-pie'),
(106, 'fas fa-check'),
(107, 'fas fa-check-circle'),
(108, 'fas fa-check-square'),
(109, 'fas fa-chess'),
(110, 'fas fa-chess-bishop'),
(111, 'fas fa-chess-board'),
(112, 'fas fa-chess-king'),
(113, 'fas fa-chess-knight'),
(114, 'fas fa-chess-pawn'),
(115, 'fas fa-chess-queen'),
(116, 'fas fa-chess-rook'),
(117, 'fas fa-chevron-circle-down'),
(118, 'fas fa-chevron-circle-left'),
(119, 'fas fa-chevron-circle-right'),
(120, 'fas fa-chevron-circle-up'),
(121, 'fas fa-chevron-down'),
(122, 'fas fa-chevron-left'),
(123, 'fas fa-chevron-right'),
(124, 'fas fa-chevron-up'),
(125, 'fas fa-child'),
(126, 'fas fa-circle'),
(127, 'fas fa-circle-notch'),
(128, 'fas fa-clipboard'),
(129, 'fas fa-clipboard-check'),
(130, 'fas fa-clipboard-list'),
(131, 'fas fa-clock'),
(132, 'fas fa-clone'),
(133, 'fas fa-closed-captioning'),
(134, 'fas fa-cloud'),
(135, 'fas fa-cloud-download-alt'),
(136, 'fas fa-cloud-upload-alt'),
(137, 'fas fa-code'),
(138, 'fas fa-code-branch'),
(139, 'fas fa-coffee'),
(140, 'fas fa-cog'),
(141, 'fas fa-cogs'),
(142, 'fas fa-columns'),
(143, 'fas fa-comment'),
(144, 'fas fa-comment-alt'),
(145, 'fas fa-comment-dots'),
(146, 'fas fa-comment-slash'),
(147, 'fas fa-comments'),
(148, 'fas fa-compass'),
(149, 'fas fa-compress'),
(150, 'fas fa-copy'),
(151, 'fas fa-copyright'),
(152, 'fas fa-couch'),
(153, 'fas fa-credit-card'),
(154, 'fas fa-crop'),
(155, 'fas fa-crosshairs'),
(156, 'fas fa-cube'),
(157, 'fas fa-cubes'),
(158, 'fas fa-cut'),
(159, 'fas fa-database'),
(160, 'fas fa-deaf'),
(161, 'fas fa-desktop'),
(162, 'fas fa-diagnoses'),
(163, 'fas fa-dna'),
(164, 'fas fa-dollar-sign'),
(165, 'fas fa-dolly'),
(166, 'fas fa-dolly-flatbed'),
(167, 'fas fa-donate'),
(168, 'fas fa-dot-circle'),
(169, 'fas fa-dove'),
(170, 'fas fa-download'),
(171, 'fas fa-edit'),
(172, 'fas fa-eject'),
(173, 'fas fa-ellipsis-h'),
(174, 'fas fa-ellipsis-v'),
(175, 'fas fa-envelope'),
(176, 'fas fa-envelope-open'),
(177, 'fas fa-envelope-square'),
(178, 'fas fa-eraser'),
(179, 'fas fa-euro-sign'),
(180, 'fas fa-exchange-alt'),
(181, 'fas fa-exclamation'),
(182, 'fas fa-exclamation-circle'),
(183, 'fas fa-exclamation-triangle'),
(184, 'fas fa-expand'),
(185, 'fas fa-expand-arrows-alt'),
(186, 'fas fa-external-link-alt'),
(187, 'fas fa-external-link-square-alt'),
(188, 'fas fa-eye'),
(189, 'fas fa-eye-dropper'),
(190, 'fas fa-eye-slash'),
(191, 'fas fa-fast-backward'),
(192, 'fas fa-fast-forward'),
(193, 'fas fa-fax'),
(194, 'fas fa-female'),
(195, 'fas fa-fighter-jet'),
(196, 'fas fa-file'),
(197, 'fas fa-file-alt'),
(198, 'fas fa-file-archive'),
(199, 'fas fa-file-audio'),
(200, 'fas fa-file-code'),
(201, 'fas fa-file-excel'),
(202, 'fas fa-file-image'),
(203, 'fas fa-file-medical'),
(204, 'fas fa-file-medical-alt'),
(205, 'fas fa-file-pdf'),
(206, 'fas fa-file-powerpoint'),
(207, 'fas fa-file-video'),
(208, 'fas fa-file-word'),
(209, 'fas fa-film'),
(210, 'fas fa-filter'),
(211, 'fas fa-fire'),
(212, 'fas fa-fire-extinguisher'),
(213, 'fas fa-first-aid'),
(214, 'fas fa-flag'),
(215, 'fas fa-flag-checkered'),
(216, 'fas fa-flask'),
(217, 'fas fa-folder'),
(218, 'fas fa-folder-open'),
(219, 'fas fa-font'),
(220, 'fas fa-football-ball'),
(221, 'fas fa-forward'),
(222, 'fas fa-frown'),
(223, 'fas fa-futbol'),
(224, 'fas fa-gamepad'),
(225, 'fas fa-gavel'),
(226, 'fas fa-gem'),
(227, 'fas fa-genderless'),
(228, 'fas fa-gift'),
(229, 'fas fa-glass-martini'),
(230, 'fas fa-globe'),
(231, 'fas fa-golf-ball'),
(232, 'fas fa-graduation-cap'),
(233, 'fas fa-h-square'),
(234, 'fas fa-hand-holding'),
(235, 'fas fa-hand-holding-heart'),
(236, 'fas fa-hand-holding-usd'),
(237, 'fas fa-hand-lizard'),
(238, 'fas fa-hand-paper'),
(239, 'fas fa-hand-peace'),
(240, 'fas fa-hand-point-down'),
(241, 'fas fa-hand-point-left'),
(242, 'fas fa-hand-point-right'),
(243, 'fas fa-hand-point-up'),
(244, 'fas fa-hand-pointer'),
(245, 'fas fa-hand-rock'),
(246, 'fas fa-hand-scissors'),
(247, 'fas fa-hand-spock'),
(248, 'fas fa-hands'),
(249, 'fas fa-hands-helping'),
(250, 'fas fa-handshake'),
(251, 'fas fa-hashtag'),
(252, 'fas fa-hdd'),
(253, 'fas fa-heading'),
(254, 'fas fa-headphones'),
(255, 'fas fa-heart'),
(256, 'fas fa-heartbeat'),
(257, 'fas fa-history'),
(258, 'fas fa-hockey-puck'),
(259, 'fas fa-home'),
(260, 'fas fa-hospital'),
(261, 'fas fa-hospital-alt'),
(262, 'fas fa-hospital-symbol'),
(263, 'fas fa-hourglass'),
(264, 'fas fa-hourglass-end'),
(265, 'fas fa-hourglass-half'),
(266, 'fas fa-hourglass-start'),
(267, 'fas fa-i-cursor'),
(268, 'fas fa-id-badge'),
(269, 'fas fa-id-card'),
(270, 'fas fa-id-card-alt'),
(271, 'fas fa-image'),
(272, 'fas fa-images'),
(273, 'fas fa-inbox'),
(274, 'fas fa-indent'),
(275, 'fas fa-industry'),
(276, 'fas fa-info'),
(277, 'fas fa-info-circle'),
(278, 'fas fa-italic'),
(279, 'fas fa-key'),
(280, 'fas fa-keyboard'),
(281, 'fas fa-language'),
(282, 'fas fa-laptop'),
(283, 'fas fa-leaf'),
(284, 'fas fa-lemon'),
(285, 'fas fa-level-down-alt'),
(286, 'fas fa-level-up-alt'),
(287, 'fas fa-life-ring'),
(288, 'fas fa-lightbulb'),
(289, 'fas fa-link'),
(290, 'fas fa-lira-sign'),
(291, 'fas fa-list'),
(292, 'fas fa-list-alt'),
(293, 'fas fa-list-ol'),
(294, 'fas fa-list-ul'),
(295, 'fas fa-location-arrow'),
(296, 'fas fa-lock'),
(297, 'fas fa-lock-open'),
(298, 'fas fa-long-arrow-alt-down'),
(299, 'fas fa-long-arrow-alt-left'),
(300, 'fas fa-long-arrow-alt-right'),
(301, 'fas fa-long-arrow-alt-up'),
(302, 'fas fa-low-vision'),
(303, 'fas fa-magic'),
(304, 'fas fa-magnet'),
(305, 'fas fa-male'),
(306, 'fas fa-map'),
(307, 'fas fa-map-marker'),
(308, 'fas fa-map-marker-alt'),
(309, 'fas fa-map-pin'),
(310, 'fas fa-map-signs'),
(311, 'fas fa-mars'),
(312, 'fas fa-mars-double'),
(313, 'fas fa-mars-stroke'),
(314, 'fas fa-mars-stroke-h'),
(315, 'fas fa-mars-stroke-v'),
(316, 'fas fa-medkit'),
(317, 'fas fa-meh'),
(318, 'fas fa-mercury'),
(319, 'fas fa-microchip'),
(320, 'fas fa-microphone'),
(321, 'fas fa-microphone-slash'),
(322, 'fas fa-minus'),
(323, 'fas fa-minus-circle'),
(324, 'fas fa-minus-square'),
(325, 'fas fa-mobile'),
(326, 'fas fa-mobile-alt'),
(327, 'fas fa-money-bill-alt'),
(328, 'fas fa-moon'),
(329, 'fas fa-motorcycle'),
(330, 'fas fa-mouse-pointer'),
(331, 'fas fa-music'),
(332, 'fas fa-neuter'),
(333, 'fas fa-newspaper'),
(334, 'fas fa-notes-medical'),
(335, 'fas fa-object-group'),
(336, 'fas fa-object-ungroup'),
(337, 'fas fa-outdent'),
(338, 'fas fa-paint-brush'),
(339, 'fas fa-pallet'),
(340, 'fas fa-paper-plane'),
(341, 'fas fa-paperclip'),
(342, 'fas fa-parachute-box'),
(343, 'fas fa-paragraph'),
(344, 'fas fa-paste'),
(345, 'fas fa-pause'),
(346, 'fas fa-pause-circle'),
(347, 'fas fa-paw'),
(348, 'fas fa-pen-square'),
(349, 'fas fa-pencil-alt'),
(350, 'fas fa-people-carry'),
(351, 'fas fa-percent'),
(352, 'fas fa-phone'),
(353, 'fas fa-phone-slash'),
(354, 'fas fa-phone-square'),
(355, 'fas fa-phone-volume'),
(356, 'fas fa-piggy-bank'),
(357, 'fas fa-pills'),
(358, 'fas fa-plane'),
(359, 'fas fa-play'),
(360, 'fas fa-play-circle'),
(361, 'fas fa-plug'),
(362, 'fas fa-plus'),
(363, 'fas fa-plus-circle'),
(364, 'fas fa-plus-square'),
(365, 'fas fa-podcast'),
(366, 'fas fa-poo'),
(367, 'fas fa-pound-sign'),
(368, 'fas fa-power-off'),
(369, 'fas fa-prescription-bottle'),
(370, 'fas fa-prescription-bottle-alt'),
(371, 'fas fa-print'),
(372, 'fas fa-procedures'),
(373, 'fas fa-puzzle-piece'),
(374, 'fas fa-qrcode'),
(375, 'fas fa-question'),
(376, 'fas fa-question-circle'),
(377, 'fas fa-quidditch'),
(378, 'fas fa-quote-left'),
(379, 'fas fa-quote-right'),
(380, 'fas fa-random'),
(381, 'fas fa-recycle'),
(382, 'fas fa-redo'),
(383, 'fas fa-redo-alt'),
(384, 'fas fa-registered'),
(385, 'fas fa-reply'),
(386, 'fas fa-reply-all'),
(387, 'fas fa-retweet'),
(388, 'fas fa-ribbon'),
(389, 'fas fa-road'),
(390, 'fas fa-rocket'),
(391, 'fas fa-rss'),
(392, 'fas fa-rss-square'),
(393, 'fas fa-ruble-sign'),
(394, 'fas fa-rupee-sign'),
(395, 'fas fa-save'),
(396, 'fas fa-search'),
(397, 'fas fa-search-minus'),
(398, 'fas fa-search-plus'),
(399, 'fas fa-seedling'),
(400, 'fas fa-server'),
(401, 'fas fa-share'),
(402, 'fas fa-share-alt'),
(403, 'fas fa-share-alt-square'),
(404, 'fas fa-share-square'),
(405, 'fas fa-shekel-sign'),
(406, 'fas fa-shield-alt'),
(407, 'fas fa-ship'),
(408, 'fas fa-shipping-fast'),
(409, 'fas fa-shopping-bag'),
(410, 'fas fa-shopping-basket'),
(411, 'fas fa-shopping-cart'),
(412, 'fas fa-shower'),
(413, 'fas fa-sign'),
(414, 'fas fa-sign-in-alt'),
(415, 'fas fa-sign-language'),
(416, 'fas fa-sign-out-alt'),
(417, 'fas fa-signal'),
(418, 'fas fa-sitemap'),
(419, 'fas fa-sliders-h'),
(420, 'fas fa-smile'),
(421, 'fas fa-smoking'),
(422, 'fas fa-snowflake'),
(423, 'fas fa-sort'),
(424, 'fas fa-sort-alpha-down'),
(425, 'fas fa-sort-alpha-up'),
(426, 'fas fa-sort-amount-down'),
(427, 'fas fa-sort-amount-up'),
(428, 'fas fa-sort-down'),
(429, 'fas fa-sort-numeric-down'),
(430, 'fas fa-sort-numeric-up'),
(431, 'fas fa-sort-up'),
(432, 'fas fa-space-shuttle'),
(433, 'fas fa-spinner'),
(434, 'fas fa-square'),
(435, 'fas fa-square-full'),
(436, 'fas fa-star'),
(437, 'fas fa-star-half'),
(438, 'fas fa-step-backward'),
(439, 'fas fa-step-forward'),
(440, 'fas fa-stethoscope'),
(441, 'fas fa-sticky-note'),
(442, 'fas fa-stop'),
(443, 'fas fa-stop-circle'),
(444, 'fas fa-stopwatch'),
(445, 'fas fa-street-view'),
(446, 'fas fa-strikethrough'),
(447, 'fas fa-subscript'),
(448, 'fas fa-subway'),
(449, 'fas fa-suitcase'),
(450, 'fas fa-sun'),
(451, 'fas fa-superscript'),
(452, 'fas fa-sync'),
(453, 'fas fa-sync-alt'),
(454, 'fas fa-syringe'),
(455, 'fas fa-table'),
(456, 'fas fa-table-tennis'),
(457, 'fas fa-tablet'),
(458, 'fas fa-tablet-alt'),
(459, 'fas fa-tablets'),
(460, 'fas fa-tachometer-alt'),
(461, 'fas fa-tag'),
(462, 'fas fa-tags'),
(463, 'fas fa-tape'),
(464, 'fas fa-tasks'),
(465, 'fas fa-taxi'),
(466, 'fas fa-terminal'),
(467, 'fas fa-text-height'),
(468, 'fas fa-text-width'),
(469, 'fas fa-th'),
(470, 'fas fa-th-large'),
(471, 'fas fa-th-list'),
(472, 'fas fa-thermometer'),
(473, 'fas fa-thermometer-empty'),
(474, 'fas fa-thermometer-full'),
(475, 'fas fa-thermometer-half'),
(476, 'fas fa-thermometer-quarter'),
(477, 'fas fa-thermometer-three-quarters'),
(478, 'fas fa-thumbs-down'),
(479, 'fas fa-thumbs-up'),
(480, 'fas fa-thumbtack'),
(481, 'fas fa-ticket-alt'),
(482, 'fas fa-times'),
(483, 'fas fa-times-circle'),
(484, 'fas fa-tint'),
(485, 'fas fa-toggle-off'),
(486, 'fas fa-toggle-on'),
(487, 'fas fa-trademark'),
(488, 'fas fa-train'),
(489, 'fas fa-transgender'),
(490, 'fas fa-transgender-alt'),
(491, 'fas fa-trash'),
(492, 'fas fa-trash-alt'),
(493, 'fas fa-tree'),
(494, 'fas fa-trophy'),
(495, 'fas fa-truck'),
(496, 'fas fa-truck-loading'),
(497, 'fas fa-truck-moving'),
(498, 'fas fa-tty'),
(499, 'fas fa-tv'),
(500, 'fas fa-umbrella'),
(501, 'fas fa-underline'),
(502, 'fas fa-undo'),
(503, 'fas fa-undo-alt'),
(504, 'fas fa-universal-access'),
(505, 'fas fa-university'),
(506, 'fas fa-unlink'),
(507, 'fas fa-unlock'),
(508, 'fas fa-unlock-alt'),
(509, 'fas fa-upload'),
(510, 'fas fa-user'),
(511, 'fas fa-user-circle'),
(512, 'fas fa-user-md'),
(513, 'fas fa-user-plus'),
(514, 'fas fa-user-secret'),
(515, 'fas fa-user-times'),
(516, 'fas fa-users'),
(517, 'fas fa-utensil-spoon'),
(518, 'fas fa-utensils'),
(519, 'fas fa-venus'),
(520, 'fas fa-venus-double'),
(521, 'fas fa-venus-mars'),
(522, 'fas fa-vial'),
(523, 'fas fa-vials'),
(524, 'fas fa-video'),
(525, 'fas fa-video-slash'),
(526, 'fas fa-volleyball-ball'),
(527, 'fas fa-volume-down'),
(528, 'fas fa-volume-off'),
(529, 'fas fa-volume-up'),
(530, 'fas fa-warehouse'),
(531, 'fas fa-weight'),
(532, 'fas fa-wheelchair'),
(533, 'fas fa-wifi'),
(534, 'fas fa-window-close'),
(535, 'fas fa-window-maximize'),
(536, 'fas fa-window-minimize'),
(537, 'fas fa-window-restore'),
(538, 'fas fa-wine-glass'),
(539, 'fas fa-won-sign'),
(540, 'fas fa-wrench'),
(541, 'fas fa-x-ray'),
(542, 'fas fa-yen-sign');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2022_07_04_155444_create_permission_tables', 1),
(6, '2022_07_04_155817_create_activity_log_table', 1),
(7, '2022_07_04_155818_add_event_column_to_activity_log_table', 1),
(8, '2022_07_04_155819_add_batch_uuid_column_to_activity_log_table', 1),
(9, '2022_07_05_012610_create_groups_table', 1),
(10, '2022_07_05_012643_create_icons_table', 1),
(11, '2022_07_05_014156_countries', 1),
(12, '2022_10_17_170118_create_businesses_table', 1),
(13, '2022_10_17_171020_model_has_business', 1),
(14, '2022_10_25_021103_rol_permission_for_business', 2),
(15, '2022_10_25_032333_group_for_business', 3),
(16, '2022_10_26_130744_create_system_configs_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_business`
--

CREATE TABLE `model_has_business` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  `business` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_business`
--

INSERT INTO `model_has_business` (`id`, `model_type`, `model_id`, `business`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 2, 1, '2022-10-25 08:29:38', '2022-10-25 08:29:38');

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(5, 'App\\Models\\User', 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identifier` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT ' ',
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `detail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `group` bigint(20) UNSIGNED DEFAULT NULL,
  `show_in_menu` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `business` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `identifier`, `guard_name`, `created_at`, `updated_at`, `detail`, `group`, `show_in_menu`, `deleted_at`, `business`) VALUES
(1, 'home', ' ', 'web', '2022-10-25 20:07:12', '2022-10-25 20:07:12', '<p>Pantalla de inicio</p>', 1, 1, NULL, NULL),
(2, 'business.all', 'Obtener empresas', 'web', '2022-10-26 07:29:13', '2022-10-26 07:29:13', '<p><br></p>', 4, 0, NULL, NULL),
(3, 'business.logs', 'Obtener Logs de empresa', 'web', '2022-10-26 07:29:39', '2022-10-26 07:29:39', NULL, 4, 0, NULL, NULL),
(4, 'business.index', 'Empresas', 'web', '2022-10-26 07:30:10', '2022-10-26 07:30:10', NULL, 4, 1, NULL, NULL),
(5, 'business.store', 'Guardar Empresa', 'web', '2022-10-26 07:30:29', '2022-10-26 07:33:46', '<p><br></p>', 4, 0, NULL, NULL),
(6, 'business.show', 'Mostrar Empresa', 'web', '2022-10-26 07:30:41', '2022-10-26 07:34:46', '<p><br></p>', 4, 0, NULL, NULL),
(7, 'business.edit', 'Editar Empresa', 'web', '2022-10-26 07:30:52', '2022-10-26 07:33:55', '<p><br></p>', 4, 0, NULL, NULL),
(8, 'business.update', 'Actualizar Empresa', 'web', '2022-10-26 07:31:03', '2022-10-26 07:34:01', '<p><br></p>', 4, 0, NULL, NULL),
(9, 'business.destroy', 'Eliminar Empresa', 'web', '2022-10-26 07:31:22', '2022-10-26 07:31:22', '<p><br></p>', 4, 0, NULL, NULL),
(10, 'role.index', 'Roles', 'web', '2022-10-26 07:36:26', '2022-10-26 07:36:26', NULL, 2, 1, NULL, NULL),
(11, 'role.all', 'Obtener Roles', 'web', '2022-10-26 07:37:48', '2022-10-26 07:37:48', NULL, 2, 0, NULL, NULL),
(12, 'role.store', 'Guardar Roles', 'web', '2022-10-26 07:38:03', '2022-10-26 07:38:03', NULL, 2, 0, NULL, NULL),
(13, 'role.show', 'Mostrar Roles', 'web', '2022-10-26 07:38:14', '2022-10-26 07:38:14', NULL, 2, 0, NULL, NULL),
(14, 'role.edit', 'Editar Roles', 'web', '2022-10-26 07:38:26', '2022-10-26 07:38:26', NULL, 2, 0, NULL, NULL),
(15, 'role.update', 'Actualizar Roles', 'web', '2022-10-26 07:38:35', '2022-10-26 07:38:35', NULL, 2, 0, NULL, NULL),
(16, 'role.destroy', 'Eliminar Roles', 'web', '2022-10-26 07:38:53', '2022-10-26 07:38:53', NULL, 2, 0, NULL, NULL),
(17, 'role.logs', 'Obtener log de roles', 'web', '2022-10-26 07:39:09', '2022-10-26 07:39:09', NULL, 2, 0, NULL, NULL),
(18, 'group.index', 'Grupos', 'web', '2022-10-26 07:40:43', '2022-10-26 07:40:43', NULL, 5, 1, NULL, NULL),
(19, 'group.all', 'Obtener grupos', 'web', '2022-10-26 07:40:52', '2022-10-26 07:40:52', NULL, 5, 0, NULL, NULL),
(20, 'group.store', 'Guardar grupos', 'web', '2022-10-26 07:41:00', '2022-10-26 07:41:00', NULL, 5, 0, NULL, NULL),
(21, 'group.show', 'Mostrar grupos', 'web', '2022-10-26 07:41:08', '2022-10-26 07:41:08', NULL, 5, 0, NULL, NULL),
(22, 'group.edit', 'Editar grupos', 'web', '2022-10-26 07:41:15', '2022-10-26 07:41:15', NULL, 5, 0, NULL, NULL),
(23, 'group.update', 'Actualizar grupos', 'web', '2022-10-26 07:41:23', '2022-10-26 07:41:23', NULL, 5, 0, NULL, NULL),
(24, 'group.destroy', 'Eliminar grupos', 'web', '2022-10-26 07:41:32', '2022-10-26 07:41:32', NULL, 5, 0, NULL, NULL),
(25, 'group.logs', 'Obtener log de grupos', 'web', '2022-10-26 07:41:50', '2022-10-26 07:41:50', NULL, 5, 0, NULL, NULL),
(26, 'group.assign_role', 'Asignar roles', 'web', '2022-10-26 07:42:38', '2022-10-26 07:42:38', NULL, 5, 1, NULL, NULL),
(27, 'permission.index', 'Permisos', 'web', '2022-10-26 07:43:20', '2022-10-26 07:43:20', NULL, 3, 1, NULL, NULL),
(28, 'permission.all', 'Obtener Permisos', 'web', '2022-10-26 07:43:29', '2022-10-26 07:43:29', NULL, 3, 0, NULL, NULL),
(29, 'permission.store', 'Guardar Permisos', 'web', '2022-10-26 07:43:40', '2022-10-26 07:43:40', NULL, 3, 0, NULL, NULL),
(30, 'permission.show', 'Mostrar Permisos', 'web', '2022-10-26 07:43:47', '2022-10-26 07:43:47', NULL, 3, 0, NULL, NULL),
(31, 'permission.edit', 'Editar Permisos', 'web', '2022-10-26 07:43:53', '2022-10-26 07:43:53', NULL, 3, 0, NULL, NULL),
(32, 'permission.update', 'Actualizar Permisos', 'web', '2022-10-26 07:44:03', '2022-10-26 07:44:03', NULL, 3, 0, NULL, NULL),
(33, 'permission.destroy', 'Eliminar Permisos', 'web', '2022-10-26 07:44:10', '2022-10-26 07:44:10', NULL, 3, 0, NULL, NULL),
(34, 'permission.logs', 'Obtener logs de permisos', 'web', '2022-10-26 07:44:20', '2022-10-26 07:44:20', NULL, 3, 0, NULL, NULL),
(35, 'permission.assign_role', 'Asignar roles', 'web', '2022-10-26 07:44:37', '2022-10-26 07:44:37', NULL, 3, 1, NULL, NULL),
(36, 'user.index', 'Usuarios', 'web', '2022-10-26 07:45:52', '2022-10-26 07:45:52', NULL, 6, 1, NULL, NULL),
(37, 'user.all', 'Obtener Usuarios', 'web', '2022-10-26 07:46:03', '2022-10-26 07:46:03', NULL, 6, 0, NULL, NULL),
(38, 'user.show', 'Mostrar Usuarios', 'web', '2022-10-26 07:46:14', '2022-10-26 07:46:14', NULL, 6, 0, NULL, NULL),
(39, 'user.edit', 'Editar Usuarios', 'web', '2022-10-26 07:46:21', '2022-10-26 07:46:21', NULL, 6, 0, NULL, NULL),
(40, 'user.update', 'Actualizar Usuarios', 'web', '2022-10-26 07:46:29', '2022-10-26 07:46:29', NULL, 6, 0, NULL, NULL),
(41, 'user.destroy', 'Eliminar Usuarios', 'web', '2022-10-26 07:46:40', '2022-10-26 07:46:40', NULL, 6, 0, NULL, NULL),
(42, 'user.logs', 'Obtener logs de usuarios', 'web', '2022-10-26 07:46:54', '2022-10-26 07:46:54', NULL, 6, 0, NULL, NULL),
(43, 'user.assign_role', 'Asignar roles', 'web', '2022-10-26 07:47:11', '2022-10-26 07:47:11', NULL, 6, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `public` tinyint(1) NOT NULL DEFAULT 0,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `business` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`, `public`, `icon`, `description`, `is_admin`, `deleted_at`, `business`) VALUES
(1, 'Developer', 'web', '2022-10-25 02:30:55', '2022-10-25 02:30:55', 0, 'fas fa-window-minimize', 'Is the web Developer', 1, NULL, NULL),
(2, 'Super Admin', 'web', '2022-10-25 07:35:38', '2022-10-25 07:36:03', 0, 'fab fa-readme', '<p>Es el administrador general del sistema</p>', 1, NULL, NULL),
(3, 'Cliente', 'web', '2022-10-25 07:36:34', '2022-10-25 07:36:34', 1, 'far fa-user-circle', '<p>Es el cliente del sistema</p>', 0, NULL, NULL),
(4, 'Recursos humanos', 'web', '2022-10-25 07:37:23', '2022-10-25 07:37:23', 0, 'far fa-clipboard', '<p>Encargados de contratación de personal</p>', 1, NULL, NULL),
(5, 'Vendedor EDA', 'web', '2022-10-25 07:38:07', '2022-10-25 08:15:59', 0, 'fas fa-money-bill-alt', '<p>Es el rol para hacer ventas de la empresa \"La esquina de Ales\"</p>', 1, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_configs`
--

CREATE TABLE `system_configs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` bigint(20) UNSIGNED NOT NULL,
  `business` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Administrator', 'admin@opcion.com', NULL, '$2y$10$1n0daLslNqJtkfJt8hxNGeiZHpqf5qeFXsUfEMaXwkRdNMW57NBQy', NULL, '2022-10-25 07:30:37', '2022-10-25 07:30:37', NULL),
(2, 'Gerente Esquina de Ales', 'gerencia@esquinadeales.com', NULL, '$2y$10$7rFszxRrOQxqwiPA8HNKx.ppby.01ajRJL7lUBQsmPZjAAzSubvKy', NULL, '2022-10-25 08:29:38', '2022-10-25 08:29:38', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indexes for table `businesses`
--
ALTER TABLE `businesses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `businesses_code_unique` (`code`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `data_type`
--
ALTER TABLE `data_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `groups_name_unique` (`name`),
  ADD KEY `groups_business_foreign` (`business`);

--
-- Indexes for table `groups_roles`
--
ALTER TABLE `groups_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groups_roles_role_foreign` (`role`),
  ADD KEY `groups_roles_group_foreign` (`group`);

--
-- Indexes for table `icons`
--
ALTER TABLE `icons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `icons_name_unique` (`name`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_business`
--
ALTER TABLE `model_has_business`
  ADD PRIMARY KEY (`id`),
  ADD KEY `model_has_business_business_foreign` (`business`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_business_unique` (`name`,`guard_name`,`business`),
  ADD KEY `permissions_group_foreign` (`group`),
  ADD KEY `permissions_business_foreign` (`business`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_business_unique` (`name`,`guard_name`,`business`),
  ADD KEY `roles_business_foreign` (`business`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `system_configs`
--
ALTER TABLE `system_configs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `system_configs_type_foreign` (`type`),
  ADD KEY `system_configs_business_foreign` (`business`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `businesses`
--
ALTER TABLE `businesses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `data_type`
--
ALTER TABLE `data_type`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `groups_roles`
--
ALTER TABLE `groups_roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `icons`
--
ALTER TABLE `icons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=990;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `model_has_business`
--
ALTER TABLE `model_has_business`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `system_configs`
--
ALTER TABLE `system_configs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_business_foreign` FOREIGN KEY (`business`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `groups_roles`
--
ALTER TABLE `groups_roles`
  ADD CONSTRAINT `groups_roles_group_foreign` FOREIGN KEY (`group`) REFERENCES `groups` (`id`),
  ADD CONSTRAINT `groups_roles_role_foreign` FOREIGN KEY (`role`) REFERENCES `roles` (`id`);

--
-- Constraints for table `model_has_business`
--
ALTER TABLE `model_has_business`
  ADD CONSTRAINT `model_has_business_business_foreign` FOREIGN KEY (`business`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_business_foreign` FOREIGN KEY (`business`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissions_group_foreign` FOREIGN KEY (`group`) REFERENCES `groups` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_business_foreign` FOREIGN KEY (`business`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `system_configs`
--
ALTER TABLE `system_configs`
  ADD CONSTRAINT `system_configs_business_foreign` FOREIGN KEY (`business`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `system_configs_type_foreign` FOREIGN KEY (`type`) REFERENCES `data_type` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;