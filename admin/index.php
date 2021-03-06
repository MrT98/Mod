<?php
/*
 * JohnCMS NEXT Mobile Content Management System (http://johncms.com)
 *
 * For copyright and license information, please see the LICENSE.md
 * Installing the system or redistributions of files must retain the above copyright notice.
 *
 * @link        http://johncms.com JohnCMS Project
 * @copyright   Copyright (C) JohnCMS Community
 * @license     GPL-3
 */

@ini_set("max_execution_time", "600");
define('_IN_JOHNCMS', 1);
define('_IN_JOHNADM', 1);

$id = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : 0;
$act = isset($_GET['act']) ? trim($_GET['act']) : '';
$mod = isset($_GET['mod']) ? trim($_GET['mod']) : '';
$do = isset($_REQUEST['do']) ? trim($_REQUEST['do']) : false;

require('../system/bootstrap.php');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Zend\I18n\Translator\Translator $translator */
$translator = $container->get(Zend\I18n\Translator\Translator::class);
$translator->addTranslationFilePattern('gettext', __DIR__ . '/locale', '/%s/default.mo');

// Проверяем права доступа
if ($systemUser->rights < 1) {
    header('Location: /?err');
    exit;
}

$headmod = 'admin';
$textl = _t('Admin Panel');
require('../system/head.php');

$array = [
    'forum',
    'news',
    'ads',
    'counters',
    'ip_whois',
    'languages',
    'settings',
    'smilies',
    'access',
    'antispy',
    'httpaf',
    'ipban',
    'antiflood',
    'ban_panel',
    'karma',
    'reg',
    'mail',
    'search_ip',
    'usr',
    'usr_adm',
    'usr_clean',
    'usr_del',
];

if ($act && ($key = array_search($act, $array)) !== false && file_exists('includes/' . $array[$key] . '.php')) {
    require('includes/' . $array[$key] . '.php');
} else {
    $regtotal = $db->query("SELECT COUNT(*) FROM `users` WHERE `preg`='0'")->fetchColumn();
    $bantotal = $db->query("SELECT COUNT(*) FROM `cms_ban_users` WHERE `ban_time` > '" . time() . "'")->fetchColumn();
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>' . _t('Admin Panel') . '</h4></div></div>';

    // Блок пользователей
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h3>' . _t('Users') . '</h3></div>';

    if ($regtotal && $systemUser->rights >= 6) {
        echo '<div class="list1"><span class="red"><b><a href="index.php?act=reg">' . _t('On registration') . '</a>&#160;(' . $regtotal . ')</b></span></div>';
    }

    echo '<div class="list1"><a href="index.php?act=usr">' . _t('Users') . '</a>&#160;(' . $container->get('counters')->users() . ')</div>' .
        '<div class="list1"><a href="index.php?act=usr_adm">' . _t('Administration') . '</a>&#160;(' . $db->query("SELECT COUNT(*) FROM `users` WHERE `rights` >= '1'")->fetchColumn() . ')</div>' .
        ($systemUser->rights >= 7 ? '<div class="list1"><a href="index.php?act=usr_clean">' . _t('Database cleanup') . '</a></div>' : '') .
        '<div class="list1"><a href="index.php?act=ban_panel">' . _t('Ban Panel') . '</a>&#160;(' . $bantotal . ')</div>' .
        ($systemUser->rights >= 7 ? '<div class="list1"><a href="index.php?act=antiflood">' . _t('Antiflood') . '</a></div>' : '') .
        ($systemUser->rights >= 7 ? '<div class="list1"><a href="index.php?act=karma">' . _t('Karma') . '</a></div>' : '') .
        '<div class="list1"><a href="../users/search.php">' . _t('Search by Nickname') . '</a></div>' .
        '<div class="list1"><a href="index.php?act=search_ip">' . _t('Search IP') . '</a></div>' .
        '</div>';

    if ($systemUser->rights >= 7) {
        // Блок модулей
        $spam = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE `spam`='1';")->fetchColumn();

        echo '<div class="mrt-code card shadow--2dp"><div class="phdr">';
        echo '<h3>' . _t('Modules') . '</h3></div>' .
            '<div class="list1"><a href="index.php?act=forum">' . _t('Forum') . '</a></div>' .
            '<div class="list1"><a href="index.php?act=news">' . _t('News') . '</a></div>' .
            '<div class="list1"><a href="index.php?act=ads">' . _t('Advertisement') . '</a></div>';

        if ($systemUser->rights == 9) {
            echo '<div class="list1"><a href="index.php?act=counters">' . _t('Counters') . '</a></div>';
        }

        echo '</div>';

        // Блок системных настроек
        echo '<div class="mrt-code card shadow--2dp"><div class="phdr">' .
            '<h3>' . _t('System') . '</h3>' .
            '</div>' .
            ($systemUser->rights == 9 ? '<div class="list1"><a href="index.php?act=settings"><b>' . _t('System Settings') . '</b></a></div>' : '') .
            '<div class="list1"><a href="index.php?act=smilies">' . _t('Update Smilies') . '</a></div>' .
            ($systemUser->rights == 9 ? '<div class="list1"><a href="index.php?act=languages">' . _t('Language Settings') . '</a></div>' : '') .
            '<div class="list1"><a href="index.php?act=access">' . _t('Permissions') . '</a></div>' .
            '</div>';

        // Блок безопасности
        echo '<div class="mrt-code card shadow--2dp"><div class="phdr">' .
            '<h3>' . _t('Security') . '</h3>' .
            '</div>' .
            '<div class="list1"><a href="index.php?act=antispy">' . _t('Anti-Spyware') . '</a></div>' .
            ($systemUser->rights == 9 ? '<div class="list1"><a href="index.php?act=ipban">' . _t('Ban by IP') . '</a></div>' : '') .
            '</div>';
    }
}

require('../system/end.php');
