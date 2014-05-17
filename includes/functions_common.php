<?php
//@todo this is a check
/**
 * MIT License
 * ===========
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 *
 * @author      Serban Ghita <serbanghita@gmail.com>
 *              Victor Stanciu <vic.stanciu@gmail.com> (until v. 1.0)
 * @license     MIT License https://github.com/serbanghita/Mobile-Detect/blob/master/LICENSE.txt
 * @link        Official page: http://mobiledetect.net
 *              GitHub Repository: https://github.com/serbanghita/Mobile-Detect
 *              Google Code Old Page: http://code.google.com/p/php-mobile-detect/
 * @version     2.7.1
 */

class Mobile_Detect
{
    /**
     * Mobile detection type.
     *
     * @deprecated since version 2.6.9
     */
    const DETECTION_TYPE_MOBILE     = 'mobile';

    /**
     * Extended detection type.
     *
     * @deprecated since version 2.6.9
     */
    const DETECTION_TYPE_EXTENDED   = 'extended';

    /**
     * A frequently used regular expression to extract version #s.
     *
     * @deprecated since version 2.6.9
     */
    const VER                       = '([\w._\+]+)';

    /**
     * Top-level device.
     */
    const MOBILE_GRADE_A            = 'A';

    /**
     * Mid-level device.
     */
    const MOBILE_GRADE_B            = 'B';

    /**
     * Low-level device.
     */
    const MOBILE_GRADE_C            = 'C';

    /**
     * Stores the version number of the current release.
     */
    const VERSION                   = '2.7.1';

    /**
     * A type for the version() method indicating a string return value.
     */
    const VERSION_TYPE_STRING       = 'text';

    /**
     * A type for the version() method indicating a float return value.
     */
    const VERSION_TYPE_FLOAT        = 'float';

    /**
     * The User-Agent HTTP header is stored in here.
     * @var string
     */
    protected $userAgent = null;

    /**
     * HTTP headers in the PHP-flavor. So HTTP_USER_AGENT and SERVER_SOFTWARE.
     * @var array
     */
    protected $httpHeaders = array();

    /**
     * The detection type, using self::DETECTION_TYPE_MOBILE or self::DETECTION_TYPE_EXTENDED.
     *
     * @deprecated since version 2.6.9
     *
     * @var string
     */
    protected $detectionType = self::DETECTION_TYPE_MOBILE;

    /**
     * List of mobile devices (phones).
     *
     * @var array
     */
    protected static $phoneDevices = array(
        'iPhone'        => '\biPhone.*Mobile|\biPod', // |\biTunes
        'BlackBerry'    => 'BlackBerry|\bBB10\b|rim[0-9]+',
        'HTC'           => 'HTC|HTC.*(Sensation|Evo|Vision|Explorer|6800|8100|8900|A7272|S510e|C110e|Legend|Desire|T8282)|APX515CKT|Qtek9090|APA9292KT|HD_mini|Sensation.*Z710e|PG86100|Z715e|Desire.*(A8181|HD)|ADR6200|ADR6400L|ADR6425|001HT|Inspire 4G|Android.*\bEVO\b|T-Mobile G1',
        'Nexus'         => 'Nexus One|Nexus S|Galaxy.*Nexus|Android.*Nexus.*Mobile',
        // @todo: Is 'Dell Streak' a tablet or a phone? ;)
        'Dell'          => 'Dell.*Streak|Dell.*Aero|Dell.*Venue|DELL.*Venue Pro|Dell Flash|Dell Smoke|Dell Mini 3iX|XCD28|XCD35|\b001DL\b|\b101DL\b|\bGS01\b',
        'Motorola'      => 'Motorola|\bDroid\b.*Build|DROIDX|Android.*Xoom|HRI39|MOT-|A1260|A1680|A555|A853|A855|A953|A955|A956|Motorola.*ELECTRIFY|Motorola.*i1|i867|i940|MB200|MB300|MB501|MB502|MB508|MB511|MB520|MB525|MB526|MB611|MB612|MB632|MB810|MB855|MB860|MB861|MB865|MB870|ME501|ME502|ME511|ME525|ME600|ME632|ME722|ME811|ME860|ME863|ME865|MT620|MT710|MT716|MT720|MT810|MT870|MT917|Motorola.*TITANIUM|WX435|WX445|XT300|XT301|XT311|XT316|XT317|XT319|XT320|XT390|XT502|XT530|XT531|XT532|XT535|XT603|XT610|XT611|XT615|XT681|XT701|XT702|XT711|XT720|XT800|XT806|XT860|XT862|XT875|XT882|XT883|XT894|XT901|XT907|XT909|XT910|XT912|XT928|XT926|XT915|XT919|XT925',
        'Samsung'       => 'Samsung|SGH-I337|BGT-S5230|GT-B2100|GT-B2700|GT-B2710|GT-B3210|GT-B3310|GT-B3410|GT-B3730|GT-B3740|GT-B5510|GT-B5512|GT-B5722|GT-B6520|GT-B7300|GT-B7320|GT-B7330|GT-B7350|GT-B7510|GT-B7722|GT-B7800|GT-C3010|GT-C3011|GT-C3060|GT-C3200|GT-C3212|GT-C3212I|GT-C3262|GT-C3222|GT-C3300|GT-C3300K|GT-C3303|GT-C3303K|GT-C3310|GT-C3322|GT-C3330|GT-C3350|GT-C3500|GT-C3510|GT-C3530|GT-C3630|GT-C3780|GT-C5010|GT-C5212|GT-C6620|GT-C6625|GT-C6712|GT-E1050|GT-E1070|GT-E1075|GT-E1080|GT-E1081|GT-E1085|GT-E1087|GT-E1100|GT-E1107|GT-E1110|GT-E1120|GT-E1125|GT-E1130|GT-E1160|GT-E1170|GT-E1175|GT-E1180|GT-E1182|GT-E1200|GT-E1210|GT-E1225|GT-E1230|GT-E1390|GT-E2100|GT-E2120|GT-E2121|GT-E2152|GT-E2220|GT-E2222|GT-E2230|GT-E2232|GT-E2250|GT-E2370|GT-E2550|GT-E2652|GT-E3210|GT-E3213|GT-I5500|GT-I5503|GT-I5700|GT-I5800|GT-I5801|GT-I6410|GT-I6420|GT-I7110|GT-I7410|GT-I7500|GT-I8000|GT-I8150|GT-I8160|GT-I8190|GT-I8320|GT-I8330|GT-I8350|GT-I8530|GT-I8700|GT-I8703|GT-I8910|GT-I9000|GT-I9001|GT-I9003|GT-I9010|GT-I9020|GT-I9023|GT-I9070|GT-I9082|GT-I9100|GT-I9103|GT-I9220|GT-I9250|GT-I9300|GT-I9305|GT-I9500|GT-I9505|GT-M3510|GT-M5650|GT-M7500|GT-M7600|GT-M7603|GT-M8800|GT-M8910|GT-N7000|GT-S3110|GT-S3310|GT-S3350|GT-S3353|GT-S3370|GT-S3650|GT-S3653|GT-S3770|GT-S3850|GT-S5210|GT-S5220|GT-S5229|GT-S5230|GT-S5233|GT-S5250|GT-S5253|GT-S5260|GT-S5263|GT-S5270|GT-S5300|GT-S5330|GT-S5350|GT-S5360|GT-S5363|GT-S5369|GT-S5380|GT-S5380D|GT-S5560|GT-S5570|GT-S5600|GT-S5603|GT-S5610|GT-S5620|GT-S5660|GT-S5670|GT-S5690|GT-S5750|GT-S5780|GT-S5830|GT-S5839|GT-S6102|GT-S6500|GT-S7070|GT-S7200|GT-S7220|GT-S7230|GT-S7233|GT-S7250|GT-S7500|GT-S7530|GT-S7550|GT-S7562|GT-S7710|GT-S8000|GT-S8003|GT-S8500|GT-S8530|GT-S8600|SCH-A310|SCH-A530|SCH-A570|SCH-A610|SCH-A630|SCH-A650|SCH-A790|SCH-A795|SCH-A850|SCH-A870|SCH-A890|SCH-A930|SCH-A950|SCH-A970|SCH-A990|SCH-I100|SCH-I110|SCH-I400|SCH-I405|SCH-I500|SCH-I510|SCH-I515|SCH-I600|SCH-I730|SCH-I760|SCH-I770|SCH-I830|SCH-I910|SCH-I920|SCH-I959|SCH-LC11|SCH-N150|SCH-N300|SCH-R100|SCH-R300|SCH-R351|SCH-R400|SCH-R410|SCH-T300|SCH-U310|SCH-U320|SCH-U350|SCH-U360|SCH-U365|SCH-U370|SCH-U380|SCH-U410|SCH-U430|SCH-U450|SCH-U460|SCH-U470|SCH-U490|SCH-U540|SCH-U550|SCH-U620|SCH-U640|SCH-U650|SCH-U660|SCH-U700|SCH-U740|SCH-U750|SCH-U810|SCH-U820|SCH-U900|SCH-U940|SCH-U960|SCS-26UC|SGH-A107|SGH-A117|SGH-A127|SGH-A137|SGH-A157|SGH-A167|SGH-A177|SGH-A187|SGH-A197|SGH-A227|SGH-A237|SGH-A257|SGH-A437|SGH-A517|SGH-A597|SGH-A637|SGH-A657|SGH-A667|SGH-A687|SGH-A697|SGH-A707|SGH-A717|SGH-A727|SGH-A737|SGH-A747|SGH-A767|SGH-A777|SGH-A797|SGH-A817|SGH-A827|SGH-A837|SGH-A847|SGH-A867|SGH-A877|SGH-A887|SGH-A897|SGH-A927|SGH-B100|SGH-B130|SGH-B200|SGH-B220|SGH-C100|SGH-C110|SGH-C120|SGH-C130|SGH-C140|SGH-C160|SGH-C170|SGH-C180|SGH-C200|SGH-C207|SGH-C210|SGH-C225|SGH-C230|SGH-C417|SGH-C450|SGH-D307|SGH-D347|SGH-D357|SGH-D407|SGH-D415|SGH-D780|SGH-D807|SGH-D980|SGH-E105|SGH-E200|SGH-E315|SGH-E316|SGH-E317|SGH-E335|SGH-E590|SGH-E635|SGH-E715|SGH-E890|SGH-F300|SGH-F480|SGH-I200|SGH-I300|SGH-I320|SGH-I550|SGH-I577|SGH-I600|SGH-I607|SGH-I617|SGH-I627|SGH-I637|SGH-I677|SGH-I700|SGH-I717|SGH-I727|SGH-i747M|SGH-I777|SGH-I780|SGH-I827|SGH-I847|SGH-I857|SGH-I896|SGH-I897|SGH-I900|SGH-I907|SGH-I917|SGH-I927|SGH-I937|SGH-I997|SGH-J150|SGH-J200|SGH-L170|SGH-L700|SGH-M110|SGH-M150|SGH-M200|SGH-N105|SGH-N500|SGH-N600|SGH-N620|SGH-N625|SGH-N700|SGH-N710|SGH-P107|SGH-P207|SGH-P300|SGH-P310|SGH-P520|SGH-P735|SGH-P777|SGH-Q105|SGH-R210|SGH-R220|SGH-R225|SGH-S105|SGH-S307|SGH-T109|SGH-T119|SGH-T139|SGH-T209|SGH-T219|SGH-T229|SGH-T239|SGH-T249|SGH-T259|SGH-T309|SGH-T319|SGH-T329|SGH-T339|SGH-T349|SGH-T359|SGH-T369|SGH-T379|SGH-T409|SGH-T429|SGH-T439|SGH-T459|SGH-T469|SGH-T479|SGH-T499|SGH-T509|SGH-T519|SGH-T539|SGH-T559|SGH-T589|SGH-T609|SGH-T619|SGH-T629|SGH-T639|SGH-T659|SGH-T669|SGH-T679|SGH-T709|SGH-T719|SGH-T729|SGH-T739|SGH-T746|SGH-T749|SGH-T759|SGH-T769|SGH-T809|SGH-T819|SGH-T839|SGH-T919|SGH-T929|SGH-T939|SGH-T959|SGH-T989|SGH-U100|SGH-U200|SGH-U800|SGH-V205|SGH-V206|SGH-X100|SGH-X105|SGH-X120|SGH-X140|SGH-X426|SGH-X427|SGH-X475|SGH-X495|SGH-X497|SGH-X507|SGH-X600|SGH-X610|SGH-X620|SGH-X630|SGH-X700|SGH-X820|SGH-X890|SGH-Z130|SGH-Z150|SGH-Z170|SGH-ZX10|SGH-ZX20|SHW-M110|SPH-A120|SPH-A400|SPH-A420|SPH-A460|SPH-A500|SPH-A560|SPH-A600|SPH-A620|SPH-A660|SPH-A700|SPH-A740|SPH-A760|SPH-A790|SPH-A800|SPH-A820|SPH-A840|SPH-A880|SPH-A900|SPH-A940|SPH-A960|SPH-D600|SPH-D700|SPH-D710|SPH-D720|SPH-I300|SPH-I325|SPH-I330|SPH-I350|SPH-I500|SPH-I600|SPH-I700|SPH-L700|SPH-M100|SPH-M220|SPH-M240|SPH-M300|SPH-M305|SPH-M320|SPH-M330|SPH-M350|SPH-M360|SPH-M370|SPH-M380|SPH-M510|SPH-M540|SPH-M550|SPH-M560|SPH-M570|SPH-M580|SPH-M610|SPH-M620|SPH-M630|SPH-M800|SPH-M810|SPH-M850|SPH-M900|SPH-M910|SPH-M920|SPH-M930|SPH-N100|SPH-N200|SPH-N240|SPH-N300|SPH-N400|SPH-Z400|SWC-E100|SCH-i909|GT-N7100|GT-N7105|SCH-I535',
        'LG'            => '\bLG\b;|LG[- ]?(C800|C900|E400|E610|E900|E-900|F160|F180K|F180L|F180S|730|855|L160|LS840|LS970|LU6200|MS690|MS695|MS770|MS840|MS870|MS910|P500|P700|P705|VM696|AS680|AS695|AX840|C729|E970|GS505|272|C395|E739BK|E960|L55C|L75C|LS696|LS860|P769BK|P350|P500|P509|P870|UN272|US730|VS840|VS950|LN272|LN510|LS670|LS855|LW690|MN270|MN510|P509|P769|P930|UN200|UN270|UN510|UN610|US670|US740|US760|UX265|UX840|VN271|VN530|VS660|VS700|VS740|VS750|VS910|VS920|VS930|VX9200|VX11000|AX840A|LW770|P506|P925|P999)',
        'Sony'          => 'SonyST|SonyLT|SonyEricsson|SonyEricssonLT15iv|LT18i|E10i|LT28h|LT26w|SonyEricssonMT27i',
        'Asus'          => 'Asus.*Galaxy|PadFone.*Mobile',
        // @ref: http://www.micromaxinfo.com/mobiles/smartphones
        // Added because the codes might conflict with Acer Tablets.
        'Micromax'      => 'Micromax.*\b(A210|A92|A88|A72|A111|A110Q|A115|A116|A110|A90S|A26|A51|A35|A54|A25|A27|A89|A68|A65|A57|A90)\b',
        'Palm'          => 'PalmSource|Palm', // avantgo|blazer|elaine|hiptop|plucker|xiino ; @todo - complete the regex.
        'Vertu'         => 'Vertu|Vertu.*Ltd|Vertu.*Ascent|Vertu.*Ayxta|Vertu.*Constellation(F|Quest)?|Vertu.*Monika|Vertu.*Signature', // Just for fun ;)
        // @ref: http://www.pantech.co.kr/en/prod/prodList.do?gbrand=VEGA (PANTECH)
        // Most of the VEGA devices are legacy. PANTECH seem to be newer devices based on Android.
        'Pantech'       => 'PANTECH|IM-A850S|IM-A840S|IM-A830L|IM-A830K|IM-A830S|IM-A820L|IM-A810K|IM-A810S|IM-A800S|IM-T100K|IM-A725L|IM-A780L|IM-A775C|IM-A770K|IM-A760S|IM-A750K|IM-A740S|IM-A730S|IM-A720L|IM-A710K|IM-A690L|IM-A690S|IM-A650S|IM-A630K|IM-A600S|VEGA PTL21|PT003|P8010|ADR910L|P6030|P6020|P9070|P4100|P9060|P5000|CDM8992|TXT8045|ADR8995|IS11PT|P2030|P6010|P8000|PT002|IS06|CDM8999|P9050|PT001|TXT8040|P2020|P9020|P2000|P7040|P7000|C790',
        // @ref: http://www.fly-phone.com/devices/smartphones/ ; Included only smartphones.
        'Fly'           => 'IQ230|IQ444|IQ450|IQ440|IQ442|IQ441|IQ245|IQ256|IQ236|IQ255|IQ235|IQ245|IQ275|IQ240|IQ285|IQ280|IQ270|IQ260|IQ250',
        // Added simvalley mobile just for fun. They have some interesting devices.
        // @ref: http://www.simvalley.fr/telephonie---gps-_22_telephonie-mobile_telephones_.html
        'SimValley'     => '\b(SP-80|XT-930|SX-340|XT-930|SX-310|SP-360|SP60|SPT-800|SP-120|SPT-800|SP-140|SPX-5|SPX-8|SP-100|SPX-8|SPX-12)\b',
        // @Tapatalk is a mobile app; @ref: http://support.tapatalk.com/threads/smf-2-0-2-os-and-browser-detection-plugin-and-tapatalk.15565/#post-79039
        'GenericPhone'  => 'Tapatalk|PDA;|SAGEM|\bmmp\b|pocket|\bpsp\b|symbian|Smartphone|smartfon|treo|up.browser|up.link|vodafone|\bwap\b|nokia|Series40|Series60|S60|SonyEricsson|N900|MAUI.*WAP.*Browser'
    );

    /**
     * List of tablet devices.
     *
     * @var array
     */
    protected static $tabletDevices = array(
        'iPad'              => 'iPad|iPad.*Mobile', // @todo: check for mobile friendly emails topic.
        'NexusTablet'       => '^.*Android.*Nexus(((?:(?!Mobile))|(?:(\s(7|10).+))).)*$',
        'SamsungTablet'     => 'SAMSUNG.*Tablet|Galaxy.*Tab|SC-01C|GT-P1000|GT-P1003|GT-P1010|GT-P3105|GT-P6210|GT-P6800|GT-P6810|GT-P7100|GT-P7300|GT-P7310|GT-P7500|GT-P7510|SCH-I800|SCH-I815|SCH-I905|SGH-I957|SGH-I987|SGH-T849|SGH-T859|SGH-T869|SPH-P100|GT-P3100|GT-P3108|GT-P3110|GT-P5100|GT-P5110|GT-P6200|GT-P7320|GT-P7511|GT-N8000|GT-P8510|SGH-I497|SPH-P500|SGH-T779|SCH-I705|SCH-I915|GT-N8013|GT-P3113|GT-P5113|GT-P8110|GT-N8010|GT-N8005|GT-N8020|GT-P1013|GT-P6201|GT-P7501|GT-N5100|GT-N5110|SHV-E140K|SHV-E140L|SHV-E140S|SHV-E150S|SHV-E230K|SHV-E230L|SHV-E230S|SHW-M180K|SHW-M180L|SHW-M180S|SHW-M180W|SHW-M300W|SHW-M305W|SHW-M380K|SHW-M380S|SHW-M380W|SHW-M430W|SHW-M480K|SHW-M480S|SHW-M480W|SHW-M485W|SHW-M486W|SHW-M500W|GT-I9228|SCH-P739|SCH-I925|GT-I9200|GT-I9205|GT-P5200|GT-P5210|SM-T311|SM-T310|SM-T210|SM-T211|SM-P900',
        // @reference: http://www.labnol.org/software/kindle-user-agent-string/20378/
        'Kindle'            => 'Kindle|Silk.*Accelerated|Android.*\b(KFTT|KFOTE|WFJWAE)\b',
        // Only the Surface tablets with Windows RT are considered mobile.
        // @ref: http://msdn.microsoft.com/en-us/library/ie/hh920767(v=vs.85).aspx
        'SurfaceTablet'     => 'Windows NT [0-9.]+; ARM;',
        // @ref: http://shopping1.hp.com/is-bin/INTERSHOP.enfinity/WFS/WW-USSMBPublicStore-Site/en_US/-/USD/ViewStandardCatalog-Browse?CatalogCategoryID=JfIQ7EN5lqMAAAEyDcJUDwMT
        'HPTablet'          => 'HP Slate 7|HP ElitePad 900|hp-tablet|EliteBook.*Touch',
        // @note: watch out for PadFone, see #132
        'AsusTablet'        => '^.*PadFone((?!Mobile).)*$|Transformer|TF101|TF101G|TF300T|TF300TG|TF300TL|TF700T|TF700KL|TF701T|TF810C|ME171|ME301T|ME371MG|ME370T|ME372MG|ME172V|ME173X|ME400C|Slider SL101',
        'BlackBerryTablet'  => 'PlayBook|RIM Tablet',
        'HTCtablet'         => 'HTC Flyer|HTC Jetstream|HTC-P715a|HTC EVO View 4G|PG41200',
        'MotorolaTablet'    => 'xoom|sholest|MZ615|MZ605|MZ505|MZ601|MZ602|MZ603|MZ604|MZ606|MZ607|MZ608|MZ609|MZ615|MZ616|MZ617',
        'NookTablet'        => 'Android.*Nook|NookColor|nook browser|BNRV200|BNRV200A|BNTV250|BNTV250A|BNTV400|BNTV600|LogicPD Zoom2',
        // @ref: http://www.acer.ro/ac/ro/RO/content/drivers
        // @ref: http://www.packardbell.co.uk/pb/en/GB/content/download (Packard Bell is part of Acer)
        // @ref: http://us.acer.com/ac/en/US/content/group/tablets
        // @note: Can conflict with Micromax and Motorola phones codes.
        'AcerTablet'        => 'Android.*; \b(A100|A101|A110|A200|A210|A211|A500|A501|A510|A511|A700|A701|W500|W500P|W501|W501P|W510|W511|W700|G100|G100W|B1-A71|B1-710|B1-711|A1-810)\b|W3-810',
        // @ref: http://eu.computers.toshiba-europe.com/innovation/family/Tablets/1098744/banner_id/tablet_footerlink/
        // @ref: http://us.toshiba.com/tablets/tablet-finder
        // @ref: http://www.toshiba.co.jp/regza/tablet/
        'ToshibaTablet'     => 'Android.*(AT100|AT105|AT200|AT205|AT270|AT275|AT300|AT305|AT1S5|AT500|AT570|AT700|AT830)|TOSHIBA.*FOLIO',
        // @ref: http://www.nttdocomo.co.jp/english/service/developer/smart_phone/technical_info/spec/index.html
        'LGTablet'          => '\bL-06C|LG-V900|LG-V909\b',
        'FujitsuTablet'     => 'Android.*\b(F-01D|F-05E|F-10D|M532|Q572)\b',
        // Prestigio Tablets http://www.prestigio.com/support
        'PrestigioTablet'   => 'PMP3170B|PMP3270B|PMP3470B|PMP7170B|PMP3370B|PMP3570C|PMP5870C|PMP3670B|PMP5570C|PMP5770D|PMP3970B|PMP3870C|PMP5580C|PMP5880D|PMP5780D|PMP5588C|PMP7280C|PMP7280|PMP7880D|PMP5597D|PMP5597|PMP7100D|PER3464|PER3274|PER3574|PER3884|PER5274|PER5474|PMP5097CPRO|PMP5097|PMP7380D',
        // @ref: http://support.lenovo.com/en_GB/downloads/default.page?#
        'LenovoTablet'      => 'IdeaTab|S2110|S6000|K3011|A3000|A1000|A2107|A2109|A1107',
        'YarvikTablet'      => 'Android.*(TAB210|TAB211|TAB224|TAB250|TAB260|TAB264|TAB310|TAB360|TAB364|TAB410|TAB411|TAB420|TAB424|TAB450|TAB460|TAB461|TAB464|TAB465|TAB467|TAB468)',
        'MedionTablet'      => 'Android.*\bOYO\b|LIFE.*(P9212|P9514|P9516|S9512)|LIFETAB',
        'ArnovaTablet'      => 'AN10G2|AN7bG3|AN7fG3|AN8G3|AN8cG3|AN7G3|AN9G3|AN7dG3|AN7dG3ST|AN7dG3ChildPad|AN10bG3|AN10bG3DT',
        // IRU.ru Tablets http://www.iru.ru/catalog/soho/planetable/
        'IRUTablet'         => 'M702pro',
        'MegafonTablet'     => 'MegaFon V9|ZTE V9',
        // @ref: http://www.e-boda.ro/tablete-pc.html
        'EbodaTablet'       => 'E-Boda (Supreme|Impresspeed|Izzycomm|Essential)',
        // @ref: http://www.allview.ro/produse/droseries/lista-tablete-pc/
        'AllViewTablet'           => 'Allview.*(Viva|Alldro|City|Speed|All TV|Frenzy|Quasar|Shine|TX1|AX1|AX2)',
        // @reference: http://wiki.archosfans.com/index.php?title=Main_Page
        'ArchosTablet'      => '\b(101G9|80G9|A101IT)\b',
        // @ref: http://www.ainol.com/plugin.php?identifier=ainol&module=product
        'AinolTablet'       => 'NOVO7|NOVO8|NOVO10|Novo7Aurora|Novo7Basic|NOVO7PALADIN|novo9-Spark',
        // @todo: inspect http://esupport.sony.com/US/p/select-system.pl?DIRECTOR=DRIVER
        // @ref: Readers http://www.atsuhiro-me.net/ebook/sony-reader/sony-reader-web-browser
        // @ref: http://www.sony.jp/support/tablet/
        'SonyTablet'        => 'Sony.*Tablet|Xperia Tablet|Sony Tablet S|SO-03E|SGPT12|SGPT121|SGPT122|SGPT123|SGPT111|SGPT112|SGPT113|SGPT211|SGPT213|SGP311|SGP312|SGP321|EBRD1101|EBRD1102|EBRD1201',
        // @ref: db + http://www.cube-tablet.com/buy-products.html
        'CubeTablet'        => 'Android.*(K8GT|U9GT|U10GT|U16GT|U17GT|U18GT|U19GT|U20GT|U23GT|U30GT)|CUBE U8GT',
        // @ref: http://www.cobyusa.com/?p=pcat&pcat_id=3001
        'CobyTablet'        => 'MID1042|MID1045|MID1125|MID1126|MID7012|MID7014|MID7015|MID7034|MID7035|MID7036|MID7042|MID7048|MID7127|MID8042|MID8048|MID8127|MID9042|MID9740|MID9742|MID7022|MID7010',
        // @ref: http://www.match.net.cn/products.asp
        'MIDTablet'         => 'M9701|M9000|M9100|M806|M1052|M806|T703|MID701|MID713|MID710|MID727|MID760|MID830|MID728|MID933|MID125|MID810|MID732|MID120|MID930|MID800|MID731|MID900|MID100|MID820|MID735|MID980|MID130|MID833|MID737|MID960|MID135|MID860|MID736|MID140|MID930|MID835|MID733',
        // @ref: http://pdadb.net/index.php?m=pdalist&list=SMiT (NoName Chinese Tablets)
        // @ref: http://www.imp3.net/14/show.php?itemid=20454
        'SMiTTablet'        => 'Android.*(\bMID\b|MID-560|MTV-T1200|MTV-PND531|MTV-P1101|MTV-PND530)',
        // @ref: http://www.rock-chips.com/index.php?do=prod&pid=2
        'RockChipTablet'    => 'Android.*(RK2818|RK2808A|RK2918|RK3066)|RK2738|RK2808A',
        // @ref: http://www.fly-phone.com/devices/tablets/ ; http://www.fly-phone.com/service/
        'FlyTablet'         => 'IQ310|Fly Vision',
        // @ref: http://www.bqreaders.com/gb/tablets-prices-sale.html
        'bqTablet'          => 'bq.*(Elcano|Curie|Edison|Maxwell|Kepler|Pascal|Tesla|Hypatia|Platon|Newton|Livingstone|Cervantes|Avant)|Maxwell.*Lite|Maxwell.*Plus',
        // @ref: http://www.huaweidevice.com/worldwide/productFamily.do?method=index&directoryId=5011&treeId=3290
        // @ref: http://www.huaweidevice.com/worldwide/downloadCenter.do?method=index&directoryId=3372&treeId=0&tb=1&type=software (including legacy tablets)
        'HuaweiTablet'      => 'MediaPad|IDEOS S7|S7-201c|S7-202u|S7-101|S7-103|S7-104|S7-105|S7-106|S7-201|S7-Slim',
        // Nec or Medias Tab
        'NecTablet'         => '\bN-06D|\bN-08D',
        // Pantech Tablets: http://www.pantechusa.com/phones/
        'PantechTablet'     => 'Pantech.*P4100',
        // Broncho Tablets: http://www.broncho.cn/ (hard to find)
        'BronchoTablet'     => 'Broncho.*(N701|N708|N802|a710)',
        // @ref: http://versusuk.com/support.html
        'VersusTablet'      => 'TOUCHPAD.*[78910]|\bTOUCHTAB\b',
        // @ref: http://www.zync.in/index.php/our-products/tablet-phablets
        'ZyncTablet'        => 'z1000|Z99 2G|z99|z930|z999|z990|z909|Z919|z900',
        // @ref: http://www.positivoinformatica.com.br/www/pessoal/tablet-ypy/
        'PositivoTablet'    => 'TB07STA|TB10STA|TB07FTA|TB10FTA',
        // @ref: https://www.nabitablet.com/
        'NabiTablet'        => 'Android.*\bNabi',
        'KoboTablet'        => 'Kobo Touch|\bK080\b|\bVox\b Build|\bArc\b Build',
        // French Danew Tablets http://www.danew.com/produits-tablette.php
        'DanewTablet'       => 'DSlide.*\b(700|701R|702|703R|704|802|970|971|972|973|974|1010|1012)\b',
        // Texet Tablets and Readers http://www.texet.ru/tablet/
        'TexetTablet'       => 'NaviPad|TB-772A|TM-7045|TM-7055|TM-9750|TM-7016|TM-7024|TM-7026|TM-7041|TM-7043|TM-7047|TM-8041|TM-9741|TM-9747|TM-9748|TM-9751|TM-7022|TM-7021|TM-7020|TM-7011|TM-7010|TM-7023|TM-7025|TM-7037W|TM-7038W|TM-7027W|TM-9720|TM-9725|TM-9737W|TM-1020|TM-9738W|TM-9740|TM-9743W|TB-807A|TB-771A|TB-727A|TB-725A|TB-719A|TB-823A|TB-805A|TB-723A|TB-715A|TB-707A|TB-705A|TB-709A|TB-711A|TB-890HD|TB-880HD|TB-790HD|TB-780HD|TB-770HD|TB-721HD|TB-710HD|TB-434HD|TB-860HD|TB-840HD|TB-760HD|TB-750HD|TB-740HD|TB-730HD|TB-722HD|TB-720HD|TB-700HD|TB-500HD|TB-470HD|TB-431HD|TB-430HD|TB-506|TB-504|TB-446|TB-436|TB-416|TB-146SE|TB-126SE',
        // @note: Avoid detecting 'PLAYSTATION 3' as mobile.
        'PlaystationTablet' => 'Playstation.*(Portable|Vita)',
        // @ref: http://www.galapad.net/product.html
        'GalapadTablet'     => 'Android.*\bG1\b',
        // @ref: http://www.micromaxinfo.com/tablet/funbook
        'MicromaxTablet'    => 'Funbook|Micromax.*\b(P250|P560|P360|P362|P600|P300|P350|P500|P275)\b',
        // http://www.karbonnmobiles.com/products_tablet.php
        'KarbonnTablet'     => 'Android.*\b(A39|A37|A34|ST8|ST10|ST7|Smart Tab3|Smart Tab2)\b',
        // @ref: http://www.myallfine.com/Products.asp
        'AllFineTablet'     => 'Fine7 Genius|Fine7 Shine|Fine7 Air|Fine8 Style|Fine9 More|Fine10 Joy|Fine11 Wide',
        // @ref: http://www.proscanvideo.com/products-search.asp?itemClass=TABLET&itemnmbr=
        'PROSCANTablet'     => '\b(PEM63|PLT1023G|PLT1041|PLT1044|PLT1044G|PLT1091|PLT4311|PLT4311PL|PLT4315|PLT7030|PLT7033|PLT7033D|PLT7035|PLT7035D|PLT7044K|PLT7045K|PLT7045KB|PLT7071KG|PLT7072|PLT7223G|PLT7225G|PLT7777G|PLT7810K|PLT7849G|PLT7851G|PLT7852G|PLT8015|PLT8031|PLT8034|PLT8036|PLT8080K|PLT8082|PLT8088|PLT8223G|PLT8234G|PLT8235G|PLT8816K|PLT9011|PLT9045K|PLT9233G|PLT9735|PLT9760G|PLT9770G)\b',
        // @ref: http://www.yonesnav.com/products/products.php
        'YONESTablet' => 'BQ1078|BC1003|BC1077|RK9702|BC9730|BC9001|IT9001|BC7008|BC7010|BC708|BC728|BC7012|BC7030|BC7027|BC7026',
        // @ref: http://www.cjshowroom.com/eproducts.aspx?classcode=004001001
        // China manufacturer makes tablets for different small brands (eg. http://www.zeepad.net/index.html)
        'ChangJiaTablet'    => 'TPC7102|TPC7103|TPC7105|TPC7106|TPC7107|TPC7201|TPC7203|TPC7205|TPC7210|TPC7708|TPC7709|TPC7712|TPC7110|TPC8101|TPC8103|TPC8105|TPC8106|TPC8203|TPC8205|TPC8503|TPC9106|TPC9701|TPC97101|TPC97103|TPC97105|TPC97106|TPC97111|TPC97113|TPC97203|TPC97603|TPC97809|TPC97205|TPC10101|TPC10103|TPC10106|TPC10111|TPC10203|TPC10205|TPC10503',
        // @ref: http://www.gloryunion.cn/products.asp
        // @ref: http://www.allwinnertech.com/en/apply/mobile.html
        // @ref: http://www.ptcl.com.pk/pd_content.php?pd_id=284 (EVOTAB)
        // aka. Cute or Cool tablets. Not sure yet, must research to avoid collisions.
        'GUTablet'          => 'TX-A1301|TX-M9002|Q702', // A12R|D75A|D77|D79|R83|A95|A106C|R15|A75|A76|D71|D72|R71|R73|R77|D82|R85|D92|A97|D92|R91|A10F|A77F|W71F|A78F|W78F|W81F|A97F|W91F|W97F|R16G|C72|C73E|K72|K73|R96G
        // @ref: http://www.pointofview-online.com/showroom.php?shop_mode=product_listing&category_id=118
        'PointOfViewTablet' => 'TAB-P506|TAB-navi-7-3G-M|TAB-P517|TAB-P-527|TAB-P701|TAB-P703|TAB-P721|TAB-P731N|TAB-P741|TAB-P825|TAB-P905|TAB-P925|TAB-PR945|TAB-PL1015|TAB-P1025|TAB-PI1045|TAB-P1325|TAB-PROTAB[0-9]+|TAB-PROTAB25|TAB-PROTAB26|TAB-PROTAB27|TAB-PROTAB26XL|TAB-PROTAB2-IPS9|TAB-PROTAB30-IPS9|TAB-PROTAB25XXL|TAB-PROTAB26-IPS10|TAB-PROTAB30-IPS10',
        // @ref: http://www.overmax.pl/pl/katalog-produktow,p8/tablety,c14/
        // @todo: add more tests.
        'OvermaxTablet'     => 'OV-(SteelCore|NewBase|Basecore|Baseone|Exellen|Quattor|EduTab|Solution|ACTION|BasicTab|TeddyTab|MagicTab|Stream|TB-08|TB-09)',
        // @ref: http://hclmetablet.com/India/index.php
        'HCLTablet'         => 'HCL.*Tablet|Connect-3G-2.0|Connect-2G-2.0|ME Tablet U1|ME Tablet U2|ME Tablet G1|ME Tablet X1|ME Tablet Y2|ME Tablet Sync',
        // @ref: http://www.edigital.hu/Tablet_es_e-book_olvaso/Tablet-c18385.html
        'DPSTablet'         => 'DPS Dream 9|DPS Dual 7',
        // @ref: http://www.telstra.com.au/home-phone/thub-2/
        'TelstraTablet'     => 'T-Hub2',
        'GenericTablet'     => 'Android.*\b97D\b|Tablet(?!.*PC)|ViewPad7|BNTV250A|MID-WCDMA|LogicPD Zoom2|\bA7EB\b|CatNova8|A1_07|CT704|CT1002|\bM721\b|rk30sdk|\bEVOTAB\b|SmartTabII10',
    );

    /**
     * List of mobile Operating Systems.
     *
     * @var array
     */
    protected static $operatingSystems = array(
        'AndroidOS'         => 'Android',
        'BlackBerryOS'      => 'blackberry|\bBB10\b|rim tablet os',
        'PalmOS'            => 'PalmOS|avantgo|blazer|elaine|hiptop|palm|plucker|xiino',
        'SymbianOS'         => 'Symbian|SymbOS|Series60|Series40|SYB-[0-9]+|\bS60\b',
        // @reference: http://en.wikipedia.org/wiki/Windows_Mobile
        'WindowsMobileOS'   => 'Windows CE.*(PPC|Smartphone|Mobile|[0-9]{3}x[0-9]{3})|Window Mobile|Windows Phone [0-9.]+|WCE;',
        // @reference: http://en.wikipedia.org/wiki/Windows_Phone
        // http://wifeng.cn/?r=blog&a=view&id=106
        // http://nicksnettravels.builttoroam.com/post/2011/01/10/Bogus-Windows-Phone-7-User-Agent-String.aspx
        'WindowsPhoneOS'   => 'Windows Phone 8.0|Windows Phone OS|XBLWP7|ZuneWP7',
        'iOS'               => '\biPhone.*Mobile|\biPod|\biPad',
        // http://en.wikipedia.org/wiki/MeeGo
        // @todo: research MeeGo in UAs
        'MeeGoOS'           => 'MeeGo',
        // http://en.wikipedia.org/wiki/Maemo
        // @todo: research Maemo in UAs
        'MaemoOS'           => 'Maemo',
        'JavaOS'            => 'J2ME/|\bMIDP\b|\bCLDC\b', // '|Java/' produces bug #135
        'webOS'             => 'webOS|hpwOS',
        'badaOS'            => '\bBada\b',
        'BREWOS'            => 'BREW',
    );

    /**
     * List of mobile User Agents.
     *
     * @var array
     */
    protected static $browsers = array(
        // @reference: https://developers.google.com/chrome/mobile/docs/user-agent
        'Chrome'          => '\bCrMo\b|CriOS|Android.*Chrome/[.0-9]* (Mobile)?',
        'Dolfin'          => '\bDolfin\b',
        'Opera'           => 'Opera.*Mini|Opera.*Mobi|Android.*Opera|Mobile.*OPR/[0-9.]+|Coast/[0-9.]+',
        'Skyfire'         => 'Skyfire',
        'IE'              => 'IEMobile|MSIEMobile', // |Trident/[.0-9]+
        'Firefox'         => 'fennec|firefox.*maemo|(Mobile|Tablet).*Firefox|Firefox.*Mobile',
        'Bolt'            => 'bolt',
        'TeaShark'        => 'teashark',
        'Blazer'          => 'Blazer',
        // @reference: http://developer.apple.com/library/safari/#documentation/AppleApplications/Reference/SafariWebContent/OptimizingforSafarioniPhone/OptimizingforSafarioniPhone.html#//apple_ref/doc/uid/TP40006517-SW3
        'Safari'          => 'Version.*Mobile.*Safari|Safari.*Mobile',
        // @ref: http://en.wikipedia.org/wiki/Midori_(web_browser)
        //'Midori'          => 'midori',
        'Tizen'           => 'Tizen',
        'UCBrowser'       => 'UC.*Browser|UCWEB',
        // @ref: https://github.com/serbanghita/Mobile-Detect/issues/7
        'DiigoBrowser'    => 'DiigoBrowser',
        // http://www.puffinbrowser.com/index.php
        'Puffin'            => 'Puffin',
        // @ref: http://mercury-browser.com/index.html
        'Mercury'          => '\bMercury\b',
        // @reference: http://en.wikipedia.org/wiki/Minimo
        // http://en.wikipedia.org/wiki/Vision_Mobile_Browser
        'GenericBrowser'  => 'NokiaBrowser|OviBrowser|OneBrowser|TwonkyBeamBrowser|SEMC.*Browser|FlyFlow|Minimo|NetFront|Novarra-Vision|MQQBrowser|MicroMessenger'
    );

    /**
     * Utilities.
     *
     * @var array
     */
    protected static $utilities = array(
        // Experimental. When a mobile device wants to switch to 'Desktop Mode'.
        // @ref: http://scottcate.com/technology/windows-phone-8-ie10-desktop-or-mobile/
        // @ref: https://github.com/serbanghita/Mobile-Detect/issues/57#issuecomment-15024011
        'DesktopMode' => 'WPDesktop',
        'TV'          => 'SonyDTV|HbbTV', // experimental
        'WebKit'      => '(webkit)[ /]([\w.]+)',
        'Bot'         => 'Googlebot|DoCoMo|YandexBot|bingbot|ia_archiver|AhrefsBot|Ezooms|GSLFbot|WBSearchBot|Twitterbot|TweetmemeBot|Twikle|PaperLiBot|Wotbox|UnwindFetchor|facebookexternalhit',
        'MobileBot'   => 'Googlebot-Mobile|DoCoMo|YahooSeeker/M1A1-R2D2',
        'Console'     => '\b(Nintendo|Nintendo WiiU|PLAYSTATION|Xbox)\b',
        'Watch'       => 'SM-V700',
    );

    /**
     * The individual segments that could exist in a User-Agent string. VER refers to the regular
     * expression defined in the constant self::VER.
     *
     * @var array
     */
    protected static $properties = array(

        // Build
        'Mobile'        => 'Mobile/[VER]',
        'Build'         => 'Build/[VER]',
        'Version'       => 'Version/[VER]',
        'VendorID'      => 'VendorID/[VER]',

        // Devices
        'iPad'          => 'iPad.*CPU[a-z ]+[VER]',
        'iPhone'        => 'iPhone.*CPU[a-z ]+[VER]',
        'iPod'          => 'iPod.*CPU[a-z ]+[VER]',
        //'BlackBerry'    => array('BlackBerry[VER]', 'BlackBerry [VER];'),
        'Kindle'        => 'Kindle/[VER]',

        // Browser
        'Chrome'        => array('Chrome/[VER]', 'CriOS/[VER]', 'CrMo/[VER]'),
        'Coast'         => array('Coast/[VER]'),
        'Dolfin'        => 'Dolfin/[VER]',
        // @reference: https://developer.mozilla.org/en-US/docs/User_Agent_Strings_Reference
        'Firefox'       => 'Firefox/[VER]',
        'Fennec'        => 'Fennec/[VER]',
        // @reference: http://msdn.microsoft.com/en-us/library/ms537503(v=vs.85).aspx
        'IE'      => array('IEMobile/[VER];', 'IEMobile [VER]', 'MSIE [VER];'),
        // http://en.wikipedia.org/wiki/NetFront
        'NetFront'      => 'NetFront/[VER]',
        'NokiaBrowser'  => 'NokiaBrowser/[VER]',
        'Opera'         => array( ' OPR/[VER]', 'Opera Mini/[VER]', 'Version/[VER]' ),
        'Opera Mini'    => 'Opera Mini/[VER]',
        'Opera Mobi'    => 'Version/[VER]',
        'UC Browser'    => 'UC Browser[VER]',
        'MQQBrowser'    => 'MQQBrowser/[VER]',
        'MicroMessenger' => 'MicroMessenger/[VER]',
        // @note: Safari 7534.48.3 is actually Version 5.1.
        // @note: On BlackBerry the Version is overwriten by the OS.
        'Safari'        => array( 'Version/[VER]', 'Safari/[VER]' ),
        'Skyfire'       => 'Skyfire/[VER]',
        'Tizen'         => 'Tizen/[VER]',
        'Webkit'        => 'webkit[ /][VER]',

        // Engine
        'Gecko'         => 'Gecko/[VER]',
        'Trident'       => 'Trident/[VER]',
        'Presto'        => 'Presto/[VER]',

        // OS
        'iOS'              => ' \bOS\b [VER] ',
        'Android'          => 'Android [VER]',
        'BlackBerry'       => array('BlackBerry[\w]+/[VER]', 'BlackBerry.*Version/[VER]', 'Version/[VER]'),
        'BREW'             => 'BREW [VER]',
        'Java'             => 'Java/[VER]',
        // @reference: http://windowsteamblog.com/windows_phone/b/wpdev/archive/2011/08/29/introducing-the-ie9-on-windows-phone-mango-user-agent-string.aspx
        // @reference: http://en.wikipedia.org/wiki/Windows_NT#Releases
        'Windows Phone OS' => array( 'Windows Phone OS [VER]', 'Windows Phone [VER]'),
        'Windows Phone'    => 'Windows Phone [VER]',
        'Windows CE'       => 'Windows CE/[VER]',
        // http://social.msdn.microsoft.com/Forums/en-US/windowsdeveloperpreviewgeneral/thread/6be392da-4d2f-41b4-8354-8dcee20c85cd
        'Windows NT'       => 'Windows NT [VER]',
        'Symbian'          => array('SymbianOS/[VER]', 'Symbian/[VER]'),
        'webOS'            => array('webOS/[VER]', 'hpwOS/[VER];'),
    );

    /**
     * Construct an instance of this class.
     *
     * @param array $headers Specify the headers as injection. Should be PHP _SERVER flavored.
     *                       If left empty, will use the global _SERVER['HTTP_*'] vars instead.
     * @param string $userAgent Inject the User-Agent header. If null, will use HTTP_USER_AGENT
     *                          from the $headers array instead.
     */
    public function __construct(
        array $headers = null,
        $userAgent = null
    ){
        $this->setHttpHeaders($headers);
        $this->setUserAgent($userAgent);
    }

    /**
    * Get the current script version.
    * This is useful for the demo.php file,
    * so people can check on what version they are testing
    * for mobile devices.
    *
    * @return string The version number in semantic version format.
    */
    public static function getScriptVersion()
    {
        return self::VERSION;
    }

    /**
     * Set the HTTP Headers. Must be PHP-flavored. This method will reset existing headers.
     *
     * @param array $httpHeaders The headers to set. If null, then using PHP's _SERVER to extract
     *                           the headers. The default null is left for backwards compatibilty.
     */
    public function setHttpHeaders($httpHeaders = null)
    {
        //use global _SERVER if $httpHeaders aren't defined
        if (!is_array($httpHeaders) || !count($httpHeaders)) {
            $httpHeaders = $_SERVER;
        }

        //clear existing headers
        $this->httpHeaders = array();

        //Only save HTTP headers. In PHP land, that means only _SERVER vars that
        //start with HTTP_.
        foreach ($httpHeaders as $key => $value) {
            if (substr($key,0,5) == 'HTTP_') {
                $this->httpHeaders[$key] = $value;
            }
        }
    }

    /**
     * Retrieves the HTTP headers.
     *
     * @return array
     */
    public function getHttpHeaders()
    {
        return $this->httpHeaders;
    }

    /**
     * Retrieves a particular header. If it doesn't exist, no exception/error is caused.
     * Simply null is returned.
     *
     * @param string $header The name of the header to retrieve. Can be HTTP compliant such as
     *                       "User-Agent" or "X-Device-User-Agent" or can be php-esque with the
     *                       all-caps, HTTP_ prefixed, underscore seperated awesomeness.
     *
     * @return string|null The value of the header.
     */
    public function getHttpHeader($header)
    {
        //are we using PHP-flavored headers?
        if (strpos($header, '_') === false) {
            $header = str_replace('-', '_', $header);
            $header = strtoupper($header);
        }

        //test the alternate, too
        $altHeader = 'HTTP_' . $header;

        //Test both the regular and the HTTP_ prefix
        if (isset($this->httpHeaders[$header])) {
            return $this->httpHeaders[$header];
        } elseif (isset($this->httpHeaders[$altHeader])) {
            return $this->httpHeaders[$altHeader];
        }
    }

    /**
     * Set the User-Agent to be used.
     *
     * @param string $userAgent The user agent string to set.
     */
    public function setUserAgent($userAgent = null)
    {
        if (!empty($userAgent)) {
            $this->userAgent = $userAgent;
        } else {
            $this->userAgent = $this->getHttpHeader('User-Agent');

            if (empty($this->userAgent)) {
                $this->userAgent = $this->getHttpHeader('X-Device-User-Agent');
            }

            //Header can occur on devices using Opera Mini (can expose the real device type).
            //Let's concatenate it (we need this extra info in the regexes).
            if ($operaMiniUa = $this->getHttpHeader('X-OperaMini-Phone-UA')) {
                $this->userAgent .= ' ' . $operaMiniUa;
            }
        }
    }

    /**
     * Retrieve the User-Agent.
     *
     * @return string|null The user agent if it's set.
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Set the detection type. Must be one of self::DETECTION_TYPE_MOBILE or
     * self::DETECTION_TYPE_EXTENDED. Otherwise, nothing is set.
     *
     * @deprecated since version 2.6.9
     *
     * @param string $type The type. Must be a self::DETECTION_TYPE_* constant. The default
     *                     parameter is null which will default to self::DETECTION_TYPE_MOBILE.
     */
    public function setDetectionType($type = null)
    {
        if ($type === null) {
            $type = self::DETECTION_TYPE_MOBILE;
        }

        if ($type != self::DETECTION_TYPE_MOBILE && $type != self::DETECTION_TYPE_EXTENDED) {
            return;
        }

        $this->detectionType = $type;
    }

    /**
     * Retrieve the list of known phone devices.
     *
     * @return array List of phone devices.
     */
    public static function getPhoneDevices()
    {
        return self::$phoneDevices;
    }

    /**
     * Retrieve the list of known tablet devices.
     *
     * @return array List of tablet devices.
     */
    public static function getTabletDevices()
    {
        return self::$tabletDevices;
    }

    /**
     * Alias for getBrowsers() method.
     *
     * @return array List of user agents.
     */
    public static function getUserAgents()
    {
        return self::getBrowsers();
    }

    /**
     * Retrieve the list of known browsers. Specifically, the user agents.
     *
     * @return array List of browsers / user agents.
     */
    public static function getBrowsers()
    {
        return self::$browsers;
    }

    /**
     * Retrieve the list of known utilities.
     *
     * @return array List of utilities.
     */
    public static function getUtilities()
    {
        return self::$utilities;
    }

    /**
     * Method gets the mobile detection rules. This method is used for the magic methods $detect->is*().
     *
     * @deprecated since version 2.6.9
     *
     * @return array All the rules (but not extended).
     */
    public static function getMobileDetectionRules()
    {
        static $rules;

        if (!$rules) {
            $rules = array_merge(
                self::$phoneDevices,
                self::$tabletDevices,
                self::$operatingSystems,
                self::$browsers
            );
        }

        return $rules;

    }

    /**
     * Method gets the mobile detection rules + utilities.
     * The reason this is separate is because utilities rules
     * don't necessary imply mobile. This method is used inside
     * the new $detect->is('stuff') method.
     *
     * @deprecated since version 2.6.9
     *
     * @return array All the rules + extended.
     */
    public function getMobileDetectionRulesExtended()
    {
        static $rules;

        if (!$rules) {
            // Merge all rules together.
            $rules = array_merge(
                self::$phoneDevices,
                self::$tabletDevices,
                self::$operatingSystems,
                self::$browsers,
                self::$utilities
            );
        }

        return $rules;
    }

    /**
     * Retrieve the current set of rules.
     *
     * @deprecated since version 2.6.9
     *
     * @return array
     */
    public function getRules()
    {
        if ($this->detectionType == self::DETECTION_TYPE_EXTENDED) {
            return self::getMobileDetectionRulesExtended();
        } else {
            return self::getMobileDetectionRules();
        }
    }

    /**
    * Check the HTTP headers for signs of mobile.
    * This is the fastest mobile check possible; it's used
    * inside isMobile() method.
    *
    * @return bool
    */
    public function checkHttpHeadersForMobile()
    {
        return(
            isset($this->httpHeaders['HTTP_ACCEPT']) &&
                (strpos($this->httpHeaders['HTTP_ACCEPT'], 'application/x-obml2d') !== false || // Opera Mini; @reference: http://dev.opera.com/articles/view/opera-binary-markup-language/
                 strpos($this->httpHeaders['HTTP_ACCEPT'], 'application/vnd.rim.html') !== false || // BlackBerry devices.
                 strpos($this->httpHeaders['HTTP_ACCEPT'], 'text/vnd.wap.wml') !== false ||
                 strpos($this->httpHeaders['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml') !== false) ||
            isset($this->httpHeaders['HTTP_X_WAP_PROFILE'])             || // @todo: validate
            isset($this->httpHeaders['HTTP_X_WAP_CLIENTID'])            ||
            isset($this->httpHeaders['HTTP_WAP_CONNECTION'])            ||
            isset($this->httpHeaders['HTTP_PROFILE'])                   ||
            isset($this->httpHeaders['HTTP_X_OPERAMINI_PHONE_UA'])      || // Reported by Nokia devices (eg. C3)
            isset($this->httpHeaders['HTTP_X_NOKIA_IPADDRESS'])         ||
            isset($this->httpHeaders['HTTP_X_NOKIA_GATEWAY_ID'])        ||
            isset($this->httpHeaders['HTTP_X_ORANGE_ID'])               ||
            isset($this->httpHeaders['HTTP_X_VODAFONE_3GPDPCONTEXT'])   ||
            isset($this->httpHeaders['HTTP_X_HUAWEI_USERID'])           ||
            isset($this->httpHeaders['HTTP_UA_OS'])                     || // Reported by Windows Smartphones.
            isset($this->httpHeaders['HTTP_X_MOBILE_GATEWAY'])          || // Reported by Verizon, Vodafone proxy system.
            isset($this->httpHeaders['HTTP_X_ATT_DEVICEID'])            || // Seen this on HTC Sensation. @ref: SensationXE_Beats_Z715e
            //HTTP_X_NETWORK_TYPE = WIFI
            ( isset($this->httpHeaders['HTTP_UA_CPU']) &&
                    $this->httpHeaders['HTTP_UA_CPU'] == 'ARM'          // Seen this on a HTC.
            )
        );
    }

    /**
     * Magic overloading method.
     *
     * @method boolean is[...]()
     * @param  string                 $name
     * @param  array                  $arguments
     * @return mixed
     * @throws BadMethodCallException when the method doesn't exist and doesn't start with 'is'
     */
    public function __call($name, $arguments)
    {
        //make sure the name starts with 'is', otherwise
        if (substr($name, 0, 2) != 'is') {
            throw new BadMethodCallException("No such method exists: $name");
        }

        $this->setDetectionType(self::DETECTION_TYPE_MOBILE);

        $key = substr($name, 2);

        return $this->matchUAAgainstKey($key);
    }

    /**
    * Find a detection rule that matches the current User-agent.
    *
    * @param null $userAgent deprecated
    * @return boolean
    */
    private function matchDetectionRulesAgainstUA($userAgent = null)
    {
        // Begin general search.
        foreach ($this->getRules() as $_regex) {
            if (empty($_regex)) {
                continue;
            }
            if ($this->match($_regex, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
    * Search for a certain key in the rules array.
    * If the key is found the try to match the corresponding
    * regex agains the User-Agent.
    *
    * @param string $key
    * @param null $userAgent deprecated
    * @return mixed
    */
    private function matchUAAgainstKey($key, $userAgent = null)
    {
        // Make the keys lowercase so we can match: isIphone(), isiPhone(), isiphone(), etc.
        $key = strtolower($key);

        //change the keys to lower case
        $_rules = array_change_key_case($this->getRules());

        if (array_key_exists($key, $_rules)) {
            if (empty($_rules[$key])) {
                return null;
            }

            return $this->match($_rules[$key], $userAgent);
        }

        return false;
    }

    /**
    * Check if the device is mobile.
    * Returns true if any type of mobile device detected, including special ones
    * @param null $userAgent deprecated
    * @param null $httpHeaders deprecated
    * @return bool
    */
    public function isMobile($userAgent = null, $httpHeaders = null)
    {

        if ($httpHeaders) {
            $this->setHttpHeaders($httpHeaders);
        }

        if ($userAgent) {
            $this->setUserAgent($userAgent);
        }

        $this->setDetectionType(self::DETECTION_TYPE_MOBILE);

        if ($this->checkHttpHeadersForMobile()) {
            return true;
        } else {
            return $this->matchDetectionRulesAgainstUA();
        }

    }

    /**
     * Check if the device is a tablet.
     * Return true if any type of tablet device is detected.
     *
     * @param  string $userAgent   deprecated
     * @param  array  $httpHeaders deprecated
     * @return bool
     */
    public function isTablet($userAgent = null, $httpHeaders = null)
    {
        $this->setDetectionType(self::DETECTION_TYPE_MOBILE);

        foreach (self::$tabletDevices as $_regex) {
            if ($this->match($_regex, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * This method checks for a certain property in the
     * userAgent.
     * @todo: The httpHeaders part is not yet used.
     *
     * @param $key
     * @param  string        $userAgent   deprecated
     * @param  string        $httpHeaders deprecated
     * @return bool|int|null
     */
    public function is($key, $userAgent = null, $httpHeaders = null)
    {
        // Set the UA and HTTP headers only if needed (eg. batch mode).
        if ($httpHeaders) {
            $this->setHttpHeaders($httpHeaders);
        }

        if ($userAgent) {
            $this->setUserAgent($userAgent);
        }

        $this->setDetectionType(self::DETECTION_TYPE_EXTENDED);

        return $this->matchUAAgainstKey($key);
    }

    /**
     * Retrieve the list of mobile operating systems.
     *
     * @return array The list of mobile operating systems.
     */
    public static function getOperatingSystems()
    {
        return self::$operatingSystems;
    }

    /**
     * Some detection rules are relative (not standard),
     * because of the diversity of devices, vendors and
     * their conventions in representing the User-Agent or
     * the HTTP headers.
     *
     * This method will be used to check custom regexes against
     * the User-Agent string.
     *
     * @param $regex
     * @param  string $userAgent
     * @return bool
     *
     * @todo: search in the HTTP headers too.
     */
    public function match($regex, $userAgent = null)
    {
        // Escape the special character which is the delimiter.
        $regex = str_replace('/', '\/', $regex);

        return (bool) preg_match('/'.$regex.'/is', (!empty($userAgent) ? $userAgent : $this->userAgent));
    }

    /**
     * Get the properties array.
     *
     * @return array
     */
    public static function getProperties()
    {
        return self::$properties;
    }

    /**
     * Prepare the version number.
     *
     * @todo Remove the error supression from str_replace() call.
     *
     * @param string $ver The string version, like "2.6.21.2152";
     *
     * @return float
     */
    public function prepareVersionNo($ver)
    {
        $ver = str_replace(array('_', ' ', '/'), '.', $ver);
        $arrVer = explode('.', $ver, 2);

        if (isset($arrVer[1])) {
            $arrVer[1] = @str_replace('.', '', $arrVer[1]); // @todo: treat strings versions.
        }

        return (float) implode('.', $arrVer);
    }

    /**
     * Check the version of the given property in the User-Agent.
     * Will return a float number. (eg. 2_0 will return 2.0, 4.3.1 will return 4.31)
     *
     * @param string $propertyName The name of the property. See self::getProperties() array
     *                              keys for all possible properties.
     * @param string $type Either self::VERSION_TYPE_STRING to get a string value or
     *                      self::VERSION_TYPE_FLOAT indicating a float value. This parameter
     *                      is optional and defaults to self::VERSION_TYPE_STRING. Passing an
     *                      invalid parameter will default to the this type as well.
     *
     * @return string|float The version of the property we are trying to extract.
     */
    public function version($propertyName, $type = self::VERSION_TYPE_STRING)
    {
        if (empty($propertyName)) {
            return false;
        }

        //set the $type to the default if we don't recognize the type
        if ($type != self::VERSION_TYPE_STRING && $type != self::VERSION_TYPE_FLOAT) {
            $type = self::VERSION_TYPE_STRING;
        }

        $properties = self::getProperties();

        // Check if the property exists in the properties array.
        if (array_key_exists($propertyName, $properties)) {

            // Prepare the pattern to be matched.
            // Make sure we always deal with an array (string is converted).
            $properties[$propertyName] = (array) $properties[$propertyName];

            foreach ($properties[$propertyName] as $propertyMatchString) {

                $propertyPattern = str_replace('[VER]', self::VER, $propertyMatchString);

                // Escape the special character which is the delimiter.
                $propertyPattern = str_replace('/', '\/', $propertyPattern);

                // Identify and extract the version.
                preg_match('/'.$propertyPattern.'/is', $this->userAgent, $match);

                if (!empty($match[1])) {
                    $version = ( $type == self::VERSION_TYPE_FLOAT ? $this->prepareVersionNo($match[1]) : $match[1] );

                    return $version;
                }

            }

        }

        return false;
    }

    /**
     * Retrieve the mobile grading, using self::MOBILE_GRADE_* constants.
     *
     * @return string One of the self::MOBILE_GRADE_* constants.
     */
    public function mobileGrade()
    {
        $isMobile = $this->isMobile();

        if (
            // Apple iOS 3.2-5.1 - Tested on the original iPad (4.3 / 5.0), iPad 2 (4.3), iPad 3 (5.1), original iPhone (3.1), iPhone 3 (3.2), 3GS (4.3), 4 (4.3 / 5.0), and 4S (5.1)
            $this->version('iPad', self::VERSION_TYPE_FLOAT)>=4.3 ||
            $this->version('iPhone', self::VERSION_TYPE_FLOAT)>=3.1 ||
            $this->version('iPod', self::VERSION_TYPE_FLOAT)>=3.1 ||

            // Android 2.1-2.3 - Tested on the HTC Incredible (2.2), original Droid (2.2), HTC Aria (2.1), Google Nexus S (2.3). Functional on 1.5 & 1.6 but performance may be sluggish, tested on Google G1 (1.5)
            // Android 3.1 (Honeycomb)  - Tested on the Samsung Galaxy Tab 10.1 and Motorola XOOM
            // Android 4.0 (ICS)  - Tested on a Galaxy Nexus. Note: transition performance can be poor on upgraded devices
            // Android 4.1 (Jelly Bean)  - Tested on a Galaxy Nexus and Galaxy 7
            ( $this->version('Android', self::VERSION_TYPE_FLOAT)>2.1 && $this->is('Webkit') ) ||

            // Windows Phone 7-7.5 - Tested on the HTC Surround (7.0) HTC Trophy (7.5), LG-E900 (7.5), Nokia Lumia 800
            $this->version('Windows Phone OS', self::VERSION_TYPE_FLOAT)>=7.0 ||

            // Blackberry 7 - Tested on BlackBerryÂ® Torch 9810
            // Blackberry 6.0 - Tested on the Torch 9800 and Style 9670
            $this->is('BlackBerry') && $this->version('BlackBerry', self::VERSION_TYPE_FLOAT)>=6.0 ||
            // Blackberry Playbook (1.0-2.0) - Tested on PlayBook
            $this->match('Playbook.*Tablet') ||

            // Palm WebOS (1.4-2.0) - Tested on the Palm Pixi (1.4), Pre (1.4), Pre 2 (2.0)
            ( $this->version('webOS', self::VERSION_TYPE_FLOAT)>=1.4 && $this->match('Palm|Pre|Pixi') ) ||
            // Palm WebOS 3.0  - Tested on HP TouchPad
            $this->match('hp.*TouchPad') ||

            // Firefox Mobile (12 Beta) - Tested on Android 2.3 device
            ( $this->is('Firefox') && $this->version('Firefox', self::VERSION_TYPE_FLOAT)>=12 ) ||

            // Chrome for Android - Tested on Android 4.0, 4.1 device
            ( $this->is('Chrome') && $this->is('AndroidOS') && $this->version('Android', self::VERSION_TYPE_FLOAT)>=4.0 ) ||

            // Skyfire 4.1 - Tested on Android 2.3 device
            ( $this->is('Skyfire') && $this->version('Skyfire', self::VERSION_TYPE_FLOAT)>=4.1 && $this->is('AndroidOS') && $this->version('Android', self::VERSION_TYPE_FLOAT)>=2.3 ) ||

            // Opera Mobile 11.5-12: Tested on Android 2.3
            ( $this->is('Opera') && $this->version('Opera Mobi', self::VERSION_TYPE_FLOAT)>11 && $this->is('AndroidOS') ) ||

            // Meego 1.2 - Tested on Nokia 950 and N9
            $this->is('MeeGoOS') ||

            // Tizen (pre-release) - Tested on early hardware
            $this->is('Tizen') ||

            // Samsung Bada 2.0 - Tested on a Samsung Wave 3, Dolphin browser
            // @todo: more tests here!
            $this->is('Dolfin') && $this->version('Bada', self::VERSION_TYPE_FLOAT)>=2.0 ||

            // UC Browser - Tested on Android 2.3 device
            ( ($this->is('UC Browser') || $this->is('Dolfin')) && $this->version('Android', self::VERSION_TYPE_FLOAT)>=2.3 ) ||

            // Kindle 3 and Fire  - Tested on the built-in WebKit browser for each
            ( $this->match('Kindle Fire') ||
            $this->is('Kindle') && $this->version('Kindle', self::VERSION_TYPE_FLOAT)>=3.0 ) ||

            // Nook Color 1.4.1 - Tested on original Nook Color, not Nook Tablet
            $this->is('AndroidOS') && $this->is('NookTablet') ||

            // Chrome Desktop 11-21 - Tested on OS X 10.7 and Windows 7
            $this->version('Chrome', self::VERSION_TYPE_FLOAT)>=11 && !$isMobile ||

            // Safari Desktop 4-5 - Tested on OS X 10.7 and Windows 7
            $this->version('Safari', self::VERSION_TYPE_FLOAT)>=5.0 && !$isMobile ||

            // Firefox Desktop 4-13 - Tested on OS X 10.7 and Windows 7
            $this->version('Firefox', self::VERSION_TYPE_FLOAT)>=4.0 && !$isMobile ||

            // Internet Explorer 7-9 - Tested on Windows XP, Vista and 7
            $this->version('MSIE', self::VERSION_TYPE_FLOAT)>=7.0 && !$isMobile ||

            // Opera Desktop 10-12 - Tested on OS X 10.7 and Windows 7
            // @reference: http://my.opera.com/community/openweb/idopera/
            $this->version('Opera', self::VERSION_TYPE_FLOAT)>=10 && !$isMobile

        ){
            return self::MOBILE_GRADE_A;
        }

        if (
            $this->version('iPad', self::VERSION_TYPE_FLOAT)<4.3 ||
            $this->version('iPhone', self::VERSION_TYPE_FLOAT)<3.1 ||
            $this->version('iPod', self::VERSION_TYPE_FLOAT)<3.1 ||

            // Blackberry 5.0: Tested on the Storm 2 9550, Bold 9770
            $this->is('Blackberry') && $this->version('BlackBerry', self::VERSION_TYPE_FLOAT)>=5 && $this->version('BlackBerry', self::VERSION_TYPE_FLOAT)<6 ||

            //Opera Mini (5.0-6.5) - Tested on iOS 3.2/4.3 and Android 2.3
            ( $this->version('Opera Mini', self::VERSION_TYPE_FLOAT)>=5.0 && $this->version('Opera Mini', self::VERSION_TYPE_FLOAT)<=6.5 &&
            ($this->version('Android', self::VERSION_TYPE_FLOAT)>=2.3 || $this->is('iOS')) ) ||

            // Nokia Symbian^3 - Tested on Nokia N8 (Symbian^3), C7 (Symbian^3), also works on N97 (Symbian^1)
            $this->match('NokiaN8|NokiaC7|N97.*Series60|Symbian/3') ||

            // @todo: report this (tested on Nokia N71)
            $this->version('Opera Mobi', self::VERSION_TYPE_FLOAT)>=11 && $this->is('SymbianOS')
        ){
            return self::MOBILE_GRADE_B;
        }

        if (
            // Blackberry 4.x - Tested on the Curve 8330
            $this->version('BlackBerry', self::VERSION_TYPE_FLOAT)<5.0 ||
            // Windows Mobile - Tested on the HTC Leo (WinMo 5.2)
            $this->match('MSIEMobile|Windows CE.*Mobile') || $this->version('Windows Mobile', self::VERSION_TYPE_FLOAT)<=5.2

        ){
            return self::MOBILE_GRADE_C;
        }

        //All older smartphone platforms and featurephones - Any device that doesn't support media queries
        //will receive the basic, C grade experience.
        return self::MOBILE_GRADE_C;
    }
}

function redirect($url) {
   if (!headers_sent())
       header('Location: '.$url);
   else {
       echo '<script type="text/javascript">';
       echo 'document.location.href="'.$url.'";';
       echo '</script>';
       echo '<noscript>';
       echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
       echo '</noscript>';
   }
}


function footer()
{
    ?>
    </div> <!--close the wrapper -->
<script>
    $(function() {
        $("input:button, input:submit, a.submit, a.button, button, #submit").button();
    });
    $('#toptabmenu').slidePanel({
        triggerName: '#toptabmenutrigger',
        position: 'fixed',
        triggerTopPos: '40px',
        panelTopPos: '40px',
        panelOpacity: 0.8,
        speed: 'fast',
        ajax: false,
        ajaxSource: null,
        clickOutsideToClose: true
    });
    function keepAlive()
    {
        $.ajax({
           type: "POST",
           url: "includes/keepAlive.php",
           data: {live:1,cb:Math.random()}
        });
    }
    
        
    window.setInterval("keepAlive()", 300000);
    window.setInterval("checkForSystemAlerts()", 150000);
</script>
</body>
</html>
    <?php
     dbclose();
    
}

function generate_random_string($length,$numeric=false)
{
    if($numeric)
    {
        $randstr='';
        while(strlen($randstr)<$length)
        {
            $randstr.=mt_rand(0,9);
        }
    } else {
        $randstr = "";
        for($i=0; $i<$length; $i++){
             $randnum = mt_rand(0,61);
             if($randnum < 10){
                $randstr .= chr($randnum+48);
             }else if($randnum < 36){
                $randstr .= chr($randnum+55);
             }else{
                $randstr .= chr($randnum+61);
             }
         } 
    }
    
  return $randstr;
}

function commaReturnTextBlockToArray($sets,$keys)
{
    $sets=explode("\n",$sets);
    $newarray=array();
    $keys=explode(",",$keys);
    if(count($sets)>0)
    {
        $i=0;
        foreach($sets as $set)
        {
            $temp=explode("|",$set);
            $j=0;
            foreach($keys as $id=>$key)
            {
                $newarray[$i][$key]=$temp[$j];
                $j++;
            }
            $i++;       
        }
    }
    return $newarray;    
}


function convertCSVtoTSV($line)
 {
     // Declare the new_line variable to make sure it starts empty
     $new_line = '';
     
     // replacement array for field data preparation
     $replacement_reg = array(
     '/[\n|\r|\t|\v|\f]/',            // remove all invisible return, newline, tabs, etc. from each individual field
     '/^(\s*|"*)(.*?)(\s*|"*)$/m',    // remove all white space from beginning and end of field
     '/^("*)(.*?)("*)$/m',            // remove all " from beginning and end of field
     '/""/',                            // replace all "" with " rfc4180-2.7
     );
     $replacement = array(
     "\\2",                            // replace all special characters
     "\\2",                            // replace all spaces at beginning and end of fields
     "\\2",                            // replace all " at beginning and end of fields
     '"',                            // reduce "" to " (if they exist)
     );
     
     // split the fields out into an array by the delimiter specified for CSV RFC
     $fields = preg_split('/,(?!(?:[^",]|[^"],[^"])+")/', $line);
     
     // process each fields cleansing superfluous chracters (spaces, control characters, and delimiting quotes)
     foreach($fields as $result){
     $result = preg_replace($replacement_reg, $replacement, $result);
     // add a tab to each new line
     $new_line .= $result."\t";
     }
     // replace the last tab with a new line feed
     $new_line = preg_replace('/\t$/', "\n", $new_line);
     return $new_line;
 }

function setUserMessage($message,$type='success',$location='right-bottom')
{
    //types can be message, error and warning
    if($type=='success'){$type='message';}
    $messagearray=array('text'=>addslashes($message),'type'=>$type,'location'=>$location);
    $_SESSION['messages'][]=$messagearray;
}

function showUserMessages()
{
    //lets add in any mango news that is marked as "popup"
    //get the userid as well, because we won't show them any messages that are in the mango_news_viewed table
    $userid=$_SESSION['cmsuser']['userid'];
    $sql="SELECT news_id FROM mango_news_viewed WHERE user_id=$userid";
    $dbViewed=dbselectmulti($sql);
    if($dbViewed['numrows']>0)
    {
        $viewed="AND id NOT IN (";
        foreach($dbViewed['data'] as $view)
        {
            $viewed.="$view[news_id],";
        }
        $viewed=substr($viewed,0,strlen($viewed)-1);
        $viewed.=")";
    } else {
        $viewed="";
    }
    $sql="SELECT * FROM mango_news WHERE popup=1 $viewed";
    $dbNews=dbselectmulti($sql);
    if($dbNews['numrows']>0)
    {
        foreach($dbNews['data'] as $newsitem)
        {
            //who wrote it?
            $sql="SELECT firstname,lastname FROM users WHERE id=$newsitem[post_by]";
            $dbAuthor=dbselectsingle($sql);
            $author="By: ".$dbAuthor['data']['firstname'].' '.$dbAuthor['data']['lastname'];
            $m['type']='news';
            $m['location']='right-bottom';
            $m['text']="<b>".$newsitem['headline']."</b><br>$author<br>".$newsitem['message'];
            $_SESSION['messages'][]=$m;
            
            //now add it to the mango_news_viewed so the user doesn't have to see it again
            $sql="INSERT INTO mango_news_viewed (news_id, user_id) VALUES ('$newsitem[id]', $userid)";
            $dbInsert=dbinsertquery($sql);
        }
    }
    
    if (count($_SESSION['messages'])>0)
    {
        
        print "<script>\n";
        foreach($_SESSION['messages'] as $m)
        {
            $type=$m['type'];
            $location=$m['location'];
            $message=str_replace(array("\r", "\n", "\t"), '',$m['text']);
            $message=htmlentities($message);
            if($type=='warning' || $type=='error')
            {
                $sticky='true';
                $time='5000';
            }elseif($type=='news'){
                $type='success';
                $time='3000';
                $sticky='true';
            }else{
                $time='3000';
                $sticky='false';
            }
            ?>
            $.ctNotify('<?php echo $message; ?>',{type: '<?php echo $type; ?>', isSticky: <?php echo $sticky; ?>, delay: <?php echo $time; ?>},'<?php echo $location; ?>');
            <?php
                
            
        } 
        print "</script>\n";
        unset($_SESSION['messages']);   
    }
}


function displayMessage($message,$type='error',$modal='true',$okbutton="Ok",$width=500)
{
    switch($type)
    {
        case "error":
        print "<div class='ui-widget' style='width:$width px;margin-top:20px;margin-left:auto;margin-right:auto;'>
            <div class='ui-state-error ui-corner-all' style='padding: 0pt 0.7em;'>";
                print "<p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: 0.3em;'></span> 
                <strong>Alert:</strong> $message</p>";
        print "     </div>\n</div>\n";
        break;
        
        case "success":
        print "<div class='ui-widget' style='width:$width px;margin-top:20px;margin-left:auto;margin-right:auto;'>
            <div class='ui-state-highlight ui-corner-all' style='padding: 0pt 0.7em;'>";
                print "<p><span class='ui-icon ui-icon-info' style='float: left; margin-right: 0.3em;'></span> 
                <strong>Success:</strong> $message</p>";
        print "     </div>\n</div>\n";
        break;
        
        case "dialog":
        ?>
        <div id="dialog-message" title="System Message">
              <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
              <?php echo $message ?>
        </div>
        <script>
            $(function() {
                $( "#dialog-message" ).dialog({
                    height: 200,
                    modal: <?php echo $modal ?>
                });
            });
        </script>
        <?php
        break;
        
        case "message":
        ?>
            <div id="dialog-message" title="System Message">
                <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
                <?php echo $message ?>
            </div>
            <script>
            $(function() {
                // a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
                $( "#dialog-message" ).dialog({
                    modal: <?php echo $modal ?>,
                    buttons: {
                        Ok: function() {
                            $( this ).dialog( "close" );
                        }
                    }
                });
            });
            </script>
        <?php
        break;
        
        case "confirm":
        ?>
        <div id="dialog-confirm" title="Empty the recycle bin?">
            <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>These items will be permanently deleted and cannot be recovered. Are you sure?</p>
        </div>

        <script>
            $(function() {
                $( "#dialog-confirm" ).dialog({
                    resizable: false,
                    height:200,
                    modal: <?php echo $modal ?>,
                    buttons: {
                        "<?php echo $okbutton ?>": function() {
                            $( this ).dialog( "close" );
                            return true;
                        },
                        Cancel: function() {
                            $( this ).dialog( "close" );
                            return false;
                        }
                    }
                });
            });
            </script>

        <?php
        break;
        
    }

}



function buildInsertLocations()
{
    $insertLocations=array();
    $insertLocations[0]='Please choose';
    $insertLocations=locationNesting(0,$insertLocations,'');
    return $insertLocations;    
}

function locationNesting($pid,$larray,$lead)
{
    global $siteID;
    $sql="SELECT * FROM insert_storage_locations WHERE parent_id=$pid AND site_id=$siteID ORDER BY location_order";
    $dbLocations=dbselectmulti($sql);
    if ($dbLocations['numrows']>0)
    {
        foreach($dbLocations['data'] as $location)
        {
            $locationid=$location['id'];
            $larray[$locationid]=$lead.$location['location_name'];       
            $sql="SELECT * FROM insert_storage_locations WHERE parent_id=$locationid AND site_id=$siteID ORDER BY location_order";
            $dbLocations=dbselectmulti($sql);
            if ($dbLocations['numrows']>0)
            {
                $lead.="--";
                $larray=locationNesting($locationid,$larray,$lead);
                $lead=substr($lead,0,strlen($lead)-2);
            } else {
                $larray[$locationid]=$lead.$location['location_name'];       
            }
        }
    }
    return $larray;
}

function crc32_file($fileName)
{
    $crc = hash_file("crc32b",$fileName);
    $crc = sprintf("%08x", 0x100000000 + hexdec($crc));
    return substr($crc, 6, 2) . substr($crc, 4, 2) . substr($crc, 2, 2) . substr($crc, 0, 2);
}


function dayDiff( $a, $b )
{
    // First we need to break these dates into their constituent parts:
    $gd_a = getdate( $a );
    $gd_b = getdate( $b );

    // Now recreate these timestamps, based upon noon on each day
    // The specific time doesn't matter but it must be the same each day
    $a_new = mktime( 12, 0, 0, $gd_a['mon'], $gd_a['mday'], $gd_a['year'] );

    $b_new = mktime( 12, 0, 0, $gd_b['mon'], $gd_b['mday'], $gd_b['year'] );

    // Subtract these two numbers and divide by the number of seconds in a
    // day. Round the result since crossing over a daylight savings time
    // barrier will cause this time to be off by an hour or two.
    return round( abs( $a_new - $b_new ) / 86400 );
}

function checkPermission($pageID='',$type='page')
{
    $valid=false;
    if($pageID=='')
    {
        $pageID=$_SERVER['SCRIPT_NAME'];
    }
    $pageID=str_replace("/","",$pageID);
    if (!is_numeric($pageID))
    {
        $sql="SELECT kiosk FROM core_pages WHERE filename='$pageID'";
        $dbPagePermissions=dbselectsingle($sql);
        if($dbPagePermissions['data']['kiosk']==1)
        {
            //kiosk mode page
            $_SESSION['kiosk']=true;
            return true;
        } else {
            $_SESSION['kiosk']=false;
        }
    }
    if (isset($GLOBALS['standalone']) && $GLOBALS['standalone']==true){$valid=true;}
    if (!isset($_SESSION['cmsuser']['loggedin'])){redirect('index.php?r='.$_SERVER['PHP_SELF']);}
    
    //if the user has admin privilege, just return true
    if ($_SESSION['cmsuser']['admin']==1)
    {
        return true;
    }
    $userPerms=$_SESSION['cmsuser']['permissions']; 
    if (count($userPerms)>0)
    {
        //otherwise, check for existence of specific permission
            if($type=='page')
            {
                if (!is_numeric($pageID))
                {
                    //looking up by script name rather than id
                    //get the permissions for the page that have a value of 1 -- those are required
                    $sql="SELECT A.permissionID, C.displayname FROM core_permission_page A, core_pages B, core_permission_list C WHERE A.value=1 AND A.pageID=B.id AND B.filename='$pageID' AND A.permissionID=C.id";
                    $dbPagePermissions=dbselectmulti($sql);
                } else {
                    //get the permissions for the page that have a value of 1 -- those are required
                    $sql="SELECT permissionID FROM core_permission_page WHERE value=1 AND pageID=$pageID";
                    $dbPagePermissions=dbselectmulti($sql);
                }
                if ($dbPagePermissions['numrows']>0)
                {
                    $valid=false;
                    foreach($dbPagePermissions['data'] as $permission)
                    {
                        $comparisonPermission=$permission['permissionID'];
                        if (in_array($comparisonPermission,$userPerms)){$valid=true;}
                    }
                } else {
                    $valid=true;
                }
            } else {
                //just looking for the existence of a particular permission 
                if (in_array($pageID,$userPerms)){$valid=true;}
            }
    } else {
        $valid=false;
    }
    //return true;
    return $valid;
}



function int2Time($sec)
{
    if($sec==0)
    {
        return "00:00";
    }
    //start with the number of seconds since midnight
    //ex: 2:30  == 150 minutes * 60 seconds =  9000 seconds
    $minutes=$sec/60; //now we have minutes
    $minutes=round($minutes,2);
    if($minutes>1)
    {
        $hours=round($minutes/60,2); //now we have decimal hours
        $parts=explode(".",$hours);
        $hours=$parts[0];
        if (strlen($hours)==1){$hours="0".$hours;}
        if (count($parts)>1)
        {
            $fracMin=".$minutes";
            $minutes=$fracMin*60;
            $minutes=intval($minutes); //drop any fractional seconds
            //pad minutes with a 0 if necessary
            if (strlen($minutes)==1){$minutes="0".$minutes;}
        } else {
            $minutes="00";
        }
        return "$hours:$minutes";
    } else {
        return "00:01";
    }
}

function int2TimeDecimal($sec)
{
    if($sec==0)
    {
        return "0.00";
    }
    //start with the number of seconds since midnight
    //ex: 2:30  == 150 minutes * 60 seconds =  9000 seconds
    $minutes=$sec/60; //now we have minutes
    $minutes=round($minutes,2);
    if($minutes>1)
    {
        $hours=round($minutes/60,2); //now we have decimal hours
        $parts=explode(".",$hours);
        $hours=$parts[0];
        //if (strlen($hours)==1){$hours="0".$hours;}
        //if(substr($hours,0,1)=='0' && strlen($hours)>1){$hours=substr($hours,1,1);}
        if (count($parts)>1)
        {
            $fracMin=$minutes;
            $minutes=$fracMin/60;
            $minutes=floatval($minutes); //drop any fractional seconds
            //pad minutes with a 0 if necessary
            if (strlen($minutes)==1){$minutes="0".$minutes;}
        } elseif(count($parts)==1) {
            $minutes=$hours.".00"; 
        } else {
            $minutes="0.00";
        }
        return $minutes;
    } else {
        return "0.00";
    }
}

function time2Int($time)
{
    $parts=explode(":",$time);
    $hours=intval($parts[0]);
    $minutes=intval($parts[1]);
    $hours=$hours*3600; //convert hours to seconds
    $minutes=$minutes*60; //convert minutes to seconds
    return ($hours+$minutes);

}

function secs2hms($timePassed)
{
    if ($timePassed<0){$timePassed=-$timePassed;$neg=true;}
    // Minute == 60 seconds
    // Hour == 3600 seconds
    // Day == 86400
    // Week == 604800
    $elapsedString = "";
    
    if($timePassed > 3600)
    {
        $hours = floor($timePassed / 3600);
        if ($hours==0){$hours="00";}
        $timePassed -= $hours * 3600;
        $elapsedString .= $hours.":";
    } else {
        $hours="00";
    }
    if($timePassed > 60)
    {
        $minutes = floor($timePassed / 60);
        if ($minutes==0){$minutes="00";}
        $timePassed -= $minutes * 60;
        $elapsedString .= $minutes.":";
    } else {
        $minutes="00";
    }
    if ($timePassed<10){$timePassed="0".$timePassed;}
    $elapsedString=$hours.":".$minutes;
    if ($neg){$elapsedString="-".$elapsedString;}
    return $elapsedString;
}

/*******************************************
* These are some formulas for calculating newsprint usage
* 
*/
function newsprintPagesPerPound($basisweight,$pagewidth=0,$pagelength=0,$paperdataid=0)
{
    //this function calculates the number of pages (broadsheet)/pound
    //for a given ream area, page size and basis weight
    global $paperdata;
    if($pagewidth==0){$pagewidth=$GLOBALS['broadsheetPageHeight'];}
    if($pagelength==0){$pagelength=$GLOBALS['broadsheetPageHeight'];}
    $reamarea=$paperdata[$paperdataid]['reamarea'];
    $ppp=($reamarea/($basisweight*2))/($pagewidth*$pagelength/144);
    return round($ppp,1);
}

function newsprintLinearFeet($rollweight,$basisweight,$rollwidth,$paperdataid=0)
{
    global $paperdata;
    //this function calculates the estimated linear feet on a roll
    $basissize=$paperdata[$paperdataid]['basissize'];
    $linearfeet=($basissize*$rollweight*500)/($basisweight*$rollwidth*12);
    return $linearfeet;
}

function newsprintLinearCopies($rollweight,$basisweight,$rollwidth,$paperdataid)
{
    global $paperdata;
    //this function calculates the estimated broadsheet page copies on a given roll
    $basissize=$paperdata[$paperdataid]['basissize'];
    $linearfeet=($basissize*$rollweight*500)/($basisweight*$rollwidth*12);
    $copies=$linearfeed/$GLOBALS['broadsheetPageHeight'];
    return $copies;
}

function newsprintTonnageEstimator($draw,$pages,$basisweight,$pagewidth=0,$pagelength=0,$paperdataid=0,$waste=0)
{
    global $paperdata;
    if($pagewidth==0){$pagewidth=$GLOBALS['broadsheetPageHeight'];}
    if($pagelength==0){$pagelength=$GLOBALS['broadsheetPageHeight'];}
    //this function estimates the amout of newsprint required for a specific job
    $reamarea==$paperdata[$paperdataid]['reamarea'];
    $pounds=($copies*$pages/2*$pagewidth/12*$pageheight/12*$basisweight*(1+$waste))/$reamarea;
    return $pounds;
}



function basisweightToGsm($basisweight,$paperdataid=0)
{
     //this function converts english weights to gsm weights 
    global $paperdata;
    $basissize=$paperdata[$paperdataid]['basissize'];
    $gsm=($basisweight*1406.5)/$basissize;
    return round($gsm,1);
}

function gsmToBasisweight($gsm,$paperdataid=0)
{
    //this function converts gsm weights to english weights
    global $paperdata;
    $basissize=$paperdata[$paperdataid]['basissize'];
    $basisweight=($gsm*$basissize)/1406.5;
    return round($basisweight,1); 
}

function weightRemainingOnRoll($rollremaining,$rollwidth,$paperdataid)
{
    //this function should be able to calculated the remaining weight on a roll
    global $coreDiameter,$paperdata;
    $factor=$paperdata[$paperdataid]['factor'];
    $remainingweight=(($rollremaining*$rollremaining)-($coreDiameter*$coreDiameter))*$rollwidth*$factor;
    return $remainingweight;
    
    //[(Roll Diameter²) - (Core Diameter²)] x Roll Width x Factor = Approximate Roll Weight 
}

function poundsToKilograms($pounds)
{
    return ($pounds/2.2046);
}

function kilogramsToPounds($kilograms)
{
    return ($kilograms*2.2046);
}

function feetToMeters($feet)
{
    return ($feet/3.28);
}

function metersToFeet($meters)
{
    return ($meters*3.28);
}

function inches2mm($inches)
{
    return round($inches*25.4,0);
}

function mm2inches($mm)
{
    return round($mm/25.4,2);
}

function inches2cm($inches)
{
    return round($inches*2.54,0);
}

function cm2inches($cm)
{
    return round($cm/2.54,2);
}


/*********************
* END OF NEWSPRINT FUNCTIONS
*/

function quicksort($seq) {
    if(!count($seq)) return $seq;
 
    $k = $seq[0];
    $x = $y = array();

        $length = count($seq);
        
    for($i=1; $i < $length; $i++) {    
         if($seq[$i] <= $k) {
             $x[] = $seq[$i];
         } else {
             $y[] = $seq[$i];
         }
     }
 
    return array_merge(quicksort($x), array($k), quicksort($y));
}

function displayAlert($alert)
{
    print "<script type='text/javascript'>
    alert($alert);
    </script>\n";
    
}



/**********************************************************************8
* 
* THIS SECTION IS FOR HANDLING JOB SAVING
*  ---------------------1 SECTIONS SO FAR -------------------------------
* 1 - HANDLE CHECKING FOR INSERT PLAN AND PACKAGE AND CREATE INSERT RECORD
*/

function printJob2Inserter($jobid,$createinsert=0,$debug=false)
{
    global $siteID;
    
    $sql="SELECT * FROM jobs WHERE id=$jobid";
    $dbJob=dbselectsingle($sql);
    $job=$dbJob['data'];
    $pubid=$job['pub_id'];
    $runid=$job['run_id'];
    $pubdate=$job['pub_date'];
    $drawTotal=$job['draw'];
    $insertpubid=$job['insertpub_id'];
    $requiresInserting=$job['requires_inserting'];
    $notesInserting=$job['notes_inserting'];
    if($insertpubid=='' || $insertpubid==0){$insertpubid=$pubid;}
    //first, see if there is an existing insert with this job id, if so, then we'll just update draw, date and insert pub as needed
    $broadpages=0;
    $tabpages=0;
    $sql="SELECT * FROM inserts WHERE weprint_id=$jobid AND clone_id=0";
    $GLOBALS['notes'].= "Checking for existing insert against $jobid job.<br />";
    $dbExisting=dbselectsingle($sql);
    if($dbExisting['numrows']>0)
    {
        $GLOBALS['notes'].="Found an existing insert.<br />";
        $existingid=$dbExisting['data']['id'];
        //we have an existing insert
        $sql="SELECT * FROM job_sections WHERE job_id=$jobid";
        $dbSections=dbselectsingle($sql);
        if($dbSections['numrows']>0)
        {
            $sections=$dbSections['data'];
            for($i=1;$i<=3;$i++)
            {
                $sql="SELECT count(id) as pagecount FROM job_pages WHERE job_id=$jobid AND version=1 AND section_code='".$sections['section'.$i.'_code']."'";
                $dbPageCount=dbselectsingle($sql);
                if($dbPageCount['numrows']>0)
                {
                   $pages=$dbPageCount['data']['pagecount'];
                   if($sections['section'.$i.'_producttype']==0)
                   {
                       $broadpages+=$pages;
                   }
                   if($sections['section'.$i.'_producttype']==1 || $sections['section'.$i.'_producttype']==2)
                   {
                       $broadpages+=($pages/2);
                       $tabpages+=$pages;
                   }
                   if($sections['section'.$i.'_producttype']==3)
                   {
                       $broadpages+=($pages/4);
                       $tabpages+=($pages/2);
                   }
                }
            }   
        }
        
        $sql="UPDATE inserts SET insert_count='$drawTotal', receive_count='$drawTotal', pages='$broadpages', tab_pages='$tabpages', insert_pub_id='$insertpubid', pub_id='$pubid' WHERE id=$existingid";
        $dbUpdate=dbexecutequery($sql);
        $GLOBALS['notes'].="Updated existing insert with $sql<br />";
        $sql="UPDATE inserts_schedule SET insert_count='$drawTotal', insert_date='$pubdate' WHERE insert_id=$existingid";
        $dbUpdate=dbexecutequery($sql);
        $GLOBALS['notes'].="Updated existing insert schedule with $sql<br />";
         
    } else {
        $sql="SELECT * FROM job_sections WHERE job_id=$jobid";
        $dbSections=dbselectsingle($sql);
        if($dbSections['numrows']>0)
        {
            $sections=$dbSections['data'];
            for($i=1;$i<=3;$i++)
            {
                $sql="SELECT count(id) as pagecount FROM job_pages WHERE job_id=$jobid AND version=1 AND section_code='".$sections['section'.$i.'_code']."'";
                $dbPageCount=dbselectsingle($sql);
                if($dbPageCount['numrows']>0)
                {
                   $pages=$dbPageCount['data']['pagecount'];
                   if($sections['section'.$i.'_producttype']==0)
                   {
                       $broadpages+=$pages;
                   }
                   if($sections['section'.$i.'_producttype']==1 || $sections['section'.$i.'_producttype']==2)
                   {
                       $broadpages+=($pages/2);
                       $tabpages+=$pages;
                   }
                   if($sections['section'.$i.'_producttype']==3)
                   {
                       $broadpages+=($pages/4);
                       $tabpages+=($pages/2);
                   }
                }
            }    
        }
        
        $sql="SELECT * FROM publications WHERE id=$pubid";
        $dbPub=dbselectsingle($sql);
        $pubname=$dbPub['data']['pub_name'];
        
        $sql="SELECT * FROM publications WHERE id=$insertpubid";
        $dbInsertPub=dbselectsingle($sql);
        $buildinsertplan=$dbInsertPub['data']['insert_run']; //this checks for auto insert run creation
        
        $sql="SELECT * FROM publications_runs WHERE id=$runid";
        $dbRun=dbselectsingle($sql);
        $runinserts=$dbRun['data']['run_inserts'];
        $runname=$dbRun['data']['run_name'];
        $mess= "build insertplan was $buildinsertplan and runname was $runname and runinsert as $runinserts<br />\n";    
        $GLOBALS['notes'].=$mess;
        if($requiresInserting || $createinsert){$runinserts=true;}
        $GLOBALS['notes'].="No existing found. have runinsert set to $runinserts.<br />";
        
        //for now we are disabling building insert plans
        $buildinsertplan=false;
        
        
        /*
        if ($buildinsertplan)
        {
            //need to get the default RUN for this pub
            $pubday=date("w",strtotime($pubdate));
            $sql="SELECT * FROM publications_insertruns WHERE pub_id=$insertpubid AND run_days like '%$pubday%' AND main_run=1";
            $GLOBALS['notes'].= 'run select->'.$sql;
            $dbDRun=dbselectsingle($sql);
            if ($dbDRun['numrows']>0)
            {
                $inserterrunid=$dbDRun['data']['id'];
                $daysprev=$dbDRun['data']['days_prev'];
                $starttime=$dbDRun['data']['run_time'];
            } else {
                $inserterrunid=0;
                $starttime='22:00';
                $daysprev=1;
            }
            $inserterid=$GLOBALS['defaultInserter'];
            $sql="SELECT * FROM inserters WHERE id=$inserterid";
            $dbInserter=dbselectsingle($sql);
            $inserter=$dbInserter['data'];
            $speed=$inserter['single_out_speed'];
            if ($speed=='' || $speed==0){$speed=12000;}
            $runtime=$drawTotal/($speed/60); 
            $runtime=round($runtime,0);
            if ($runtime<30){$runtime=30;}
            
            $startdatetime=date("Y-m-d",strtotime($pubdate."-$daysprev days"))." ".$starttime;
            $stopdatetime=date("Y-m-d H:i",strtotime($startdatetime." +$runtime minutes"));
            $continue=false;
            $packagedate=date("Y-m-d",strtotime($startdatetime));
            $GLOBALS['notes'].= "<br />Runtime = $runtime <br />start $startdatetime<br />stop $stopdatetime<br />\n";
            $inserterid=$GLOBALS['defaultInserter'];
            $packdate=date("Y-m-d",strtotime($pubdate." - 1 day"));
            
            //lets check to see if we already have one first
            $sql="SELECT * FROM jobs_inserter_plans WHERE inserter_id=$inserterid AND pub_id=$insertpubid AND run_id=$insertrunid AND pub_date='$pubdate'";
            $dbCheck=dbselectmulti($sql);
            if($dbCheck['numrows']==0)
            {
                //save the plan first
                $sql="INSERT INTO jobs_inserter_plans (inserter_id,pub_id, run_id, pub_date, inserter_request, address, 
                site_id, num_packages) VALUES ('$inserterid', '$insertpubid', '$insertrunid', '$pubdate', '$drawTotal', 
                '0', '$siteID', '1')";
                $dbPlanInsert=dbinsertquery($sql);
                $planid=$dbPlanInsert['insertid'];
                $GLOBALS['notes'].= "<br />Adding a plan with $sql<br>Error (if any) is $dbPlanInsert[error]<br />\n";
                $sql="INSERT INTO jobs_inserter_packages (inserter_id, pub_date, package_date, package_startdatetime, package_stopdatetime, 
                package_name, package_runlength, double_out, pub_id,  jacket_insert_id, inserter_request) VALUES 
                ('$inserterid','$pubdate', '$packagedate', '$startdatetime', '$stopdatetime', 'Main', '$runtime', '0', 
                '$insertpubid', '0', '$drawTotal')";
                 $dbInsert=dbinsertquery($sql);
                 $error=$dbInsert['error'];
                 $packageid=$dbInsert['numrows'];    
                 $GLOBALS['notes'].= "<br />inserting package with $sql<br>Error (if any) $error\n";
                //set up the package settings
                 $sql="SELECT * FROM inserters WHERE id=$inserterid";
                 $dbInserter=dbselectsingle($sql);
                 $inserter=$dbInserter['data'];
                 $sql="INSERT INTO jobs_inserter_packages_settings (package_id, reject_misses, reject_doubles, miss_fault, double_fault, attempt_repair, gap, delivery, copies_per_bundle, turns) VALUES ('$packageid', '$inserter[reject_misses]','$inserter[reject_doubles]','$inserter[miss_fault]','$inserter[double_fault]','$inserter[attempt_repair]','$inserter[gap]','$inserter[delivery]','$inserter[copies_per_bundle]','$inserter[turns]')";
                 $dbInsert=dbinsertquery($sql);
            } else {
                $GLOBALS['notes'].= "<br />Plan and package already exist<br />\n";
            }
        }
        */
        if ($runinserts)
        {
            
            global $wePrintAdvertiserID;
            if($wePrintAdvertiserID==0){$wePrintAdvertiserID=1;}
            $rdate=date("Y-m-d");
            
            //this means that we need to book this run as an insert for this pub date and pub_id
            $sql="INSERT INTO inserts (insert_tagline, advertiser_id, insert_count, shipper, printer, ship_type, ship_quantity, receive_count, product_size, pages, tab_pages, receive_by, receive_date, weprint_id, site_id, spawned, created_datetime, created_by, received, scheduled, confirmed) VALUES ('WE PRINT - $runname','$wePrintAdvertiserID',  '$drawTotal', 'WE PRINTED', 'WE PRINTED', 'pallet',  '1','$drawTotal', 0, '$broadpages', '$tabpage', 'WE PRINTED', '$rdate','$jobid', '$siteID', 1, '".date("Y-m-d H:i:s")."',0,1,1,1)";
            if($debug){print "Inserted insert with $sql<br />";}
            $dbInsert=dbinsertquery($sql);
            $GLOBALS['notes'].="<br />inserting an insert with $sql<br />\n";
            if ($dbInsert['error']!=''){$GLOBALS['notes'].= $dbInsert['error'];}
            $insertid=$dbInsert['insertid'];
            
            $insertday=date("w",strtotime($pubdate));
            $insertdate=date("Y-m-d",strtotime($pubdate));
                                    
            //figure out if there is an insert run for this pub date
            $runsql="SELECT * FROM publications_insertruns WHERE pub_id='$pubid' AND run_days LIKE '%$insertday%'";
            $dbRun=dbselectsingle($runsql);
            if($dbRun['numrows']>0)
            {
                $runid=$dbRun['data']['id'];
            } else {
                $runid=0;
            }
            
            $schedsql="INSERT INTO inserts_schedule (insert_id, pub_id, run_id, insert_quantity, insert_date) VALUES ('$insertid', '$insertpubid', '$runid', '$drawTotal', '$insertdate')";
            $dbInsertSchedule=dbinsertquery($schedsql);
            
            $GLOBALS['notes'].="<br />adding schedule for this insert with $schedsql<br />\n";
            if ($dbInsertSchedule['error']!=''){$GLOBALS['notes'].= $dbInsertSchedule['error'];}
                
        }
    }
    
}


function printJob2Bindery($jobid)
{
    global $siteID;
    //ok, we need to look and see if there is a need for a binder job first
    $sql="SELECT * FROM jobs WHERE id=$jobid";
    $dbJob=dbselectsingle($sql);
    $job=$dbJob['data'];
    if($job['stitch']==1 || $job['trim']==1)
    {
        //yep, it's a candidate
        //lets see if it's already been created...
        $sql="SELECT * FROM bindery_jobs WHERE job_id=$jobid";
        $dbBin=dbselectsingle($sql);
        if($dbBin['numrows']>0)
        {
            //ok, we have an existing job... we'll just move on
        } else {
            $by=$_SESSION['cmsuser']['userid'];
            $dt=date("Y-m-d H:i");
            if($job['bindery_startdate']!=$job['pub_date'])
            {
                $requestStart=date("Y-m-d",strtotime($job['bindery_startdate']));
            } else {
                $requestStart=date("Y-m-d",strtotime($job['pub_date']."-5 days"));
            }
            //need to create a record for it!
            $sql="INSERT INTO bindery_jobs (pub_id, run_id, job_id, pub_date, draw, stitch, trim, quarterfold, glossy_cover,glossy_insides, glossy_cover_draw, glossy_insides_count, bindery_duedate, site_id, created_by, created_datetime,  request_startdate, notes) VALUES ('$job[pub_id]', '$job[run_id]', '$job[id]', '$job[pub_date]', '$job[draw]', '$job[stitch]', '$job[trim]', '$job[quarterfold]', '$job[glossy_cover]', '$job[glossy_insides]', '$job[glossy_cover_draw]','$job[glossy_insides_count]', '$job[bindery_duedate]', $siteID, $by, '$dt', '$requestStart', '".addslashes($job['notes_bindery'])."')";
            $dbInsert=dbinsertquery($sql);
            if($dbInsert['error']!='')
            {
                setUserMessage('There was a problem creating the bindery portion of the job.<br>'.$dbInsert['error'],'error');
            } else {
                $GLOBALS['notes'].="<br />Successfully added a bindery job record<br />\n";
            }
        }
    }    
}

function printJob2Delivery($jobid)
{
     global $siteID;
    $sql="SELECT * FROM jobs WHERE id=$jobid";
    $dbJob=dbselectsingle($sql);
    $job=$dbJob['data'];
    if($job['request_delivery']==1)
    {
        //see if the delivery record exists
        $sql="SELECT * FROM delivery_jobs WHERE job_id=$jobid";
        $dbCheck=dbselectsingle($sql);
        if($dbCheck['numrows']>0)
        {
            //exists, nothing we need to do about it.
        } else {
            //create a new delivery record
            //pull customer id from pub
            $sql="SELECT customer_id FROM publications WHERE id=$job[pub_id]";
            $dbCustomer=dbselectsingle($sql);
            $customerid=$dbCustomer['data']['customer_id'];
            
            $due=date("Y-m-d",strtotime($job['pub_date']."-2 days"));
            $sql="INSERT INTO delivery_jobs (job_id, pub_date, delivery_due, customer_id, notes) VALUES 
            ('$jobid', '$job[pub_date]', '$due', '$customerid]', '$job[notes_delivery]')";
            $dbInsert=dbinsertquery($sql);
            if($dbInsert['error']!='')
            {
                setUserMessage('There was a problem creating the delivery portion of the job.<br>'.$dbInsert['error'],'error');
            }else {
                $GLOBALS['notes'].="<br />Successfully added a delivery job record<br />\n";
            }
        }   
    }
}
function printJob2Addressing($jobid)
{
    $sql="SELECT * FROM jobs WHERE id=$jobid";
    $dbJob=dbselectsingle($sql);
    $job=$dbJob['data'];
    if($job['request_addressing']==1)
    {
        //see if the address record exists
        $sql="SELECT * FROM addressing_jobs WHERE job_id=$jobid";
        $dbCheck=dbselectsingle($sql);
        if($dbCheck['numrows']>0)
        {
            //exists, nothing we need to do about it.
        } else {
            //create a new delivery record
            //pull customer id from pub
            
            $due=date("Y-m-d",strtotime($job['pub_date']."-3 days"));
            $draw=$job['draw'];
            $sql="INSERT INTO addressing_jobs (job_id,due_date,draw) VALUES 
            ('$jobid', '$due', '$draw')";
            $dbInsert=dbinsertquery($sql);
            if($dbInsert['error']!='')
            {
                setUserMessage('There was a problem creating the addressing portion of the job.<br>'.$dbInsert['error'],'error');
            }else {
                $GLOBALS['notes'].="<br />Successfully added an addressing job record<br />\n";
            }
        }   
    }
}

function press_stats($statsid,$jobid)
 {
    global $siteID, $pressid,$sizes;
    $sql="SELECT * FROM job_stats WHERE id=$statsid";
    $dbStats=dbselectsingle($sql);
    $stats=$dbStats['data'];
    
    $sql="SELECT * FROM jobs WHERE id=$jobid";
    $dbJob=dbselectsingle($sql);
    $jobinfo=$dbJob['data'];
    $schedstart=$jobinfo['startdatetime'];
    $schedstop=$jobinfo['enddatetime'];
    $draw=$jobinfo['draw'];     
    $pubid=$jobinfo['pub_id'];
    $runid=$jobinfo['run_id'];
    $pubdate=$jobinfo['pub_date'];
    $folder=$jobinfo['folder'];
    $goodtime=$stats['goodcopy_actual'];
    $actualstart=$stats['startdatetime_actual'];
    $actualstop=$stats['stopdatetime_actual'];
    //math for run length calculation
    //get minutes between actual stop and start
    $start=strtotime($actualstart);
    $stop=strtotime($actualstop);
    
    $startoffset=round(($start-strtotime($schedstart))/60,2);
    $finishoffset=round(($stop-strtotime($schedstop))/60,2);
    $schedruntime=round((strtotime($schedstop)-strtotime($schedstart))/60,2);
    $printdate=date("Y-m-d",strtotime($startD));
    $downtime=$stats['total_downtime'];
    $runningtime=round(($stop-$start)/60,2); //should give us running time in decimal minutes
    $goodrunningtime=$runningtime-$downtime;
    
    $counterstart=$stats['counter_start'];
    $counterstop=$stats['counter_stop'];
    $spoilsstartup=$stats['spoils_startup'];
    
    //calculations for waste, speeds, spoils
    $gross=$counterstop-$counterstart;
    $spoilstotal=$gross-$draw;
    $spoilsrunning=$spoilstotal-$spoilsstartup;
    $wastepercent=round($spoilstotal/$draw*100,2);
    
    $runspeed=round($gross/($runningtime/60),0);
    $goodrunspeed=round($gross/($goodrunningtime/60),0);
    
    $layoutid=$jobinfo['layout_id'];
    $pagewidth=$jobinfo['pagewidth'];
    $totaltons=0;
    
    if ($jobinfo['data_collected']==0)
    {
        pressWearTear($jobid);       
    }
            
    
   
   
    
    $pubdate=$jobinfo['pub_date'];
    $pubid=$jobinfo['pub_id'];
    $insertpubid=$jobinfo['insert_pub_id'];
    $runid=$jobinfo['run_id'];
    if($jobinfo['redo_job_id']!=0)
    {
        //means we are in a redo, in which case we actually need the original
        //so, we need to requery with jobid = the redo job id
        $reprintid=$jobid;
        $jobid=$jobinfo['redo_job_id'];
        $sql="SELECT * FROM jobs WHERE id=$jobid";
        $dbJobInfo=dbselectsingle($sql);
        $jobinfo=$dbJobInfo['data'];
        $pubdate=$jobinfo['pub_date'];
        $pubid=$jobinfo['pub_id'];
        $runid=$jobinfo['run_id'];
        $insertpubid=$jobinfo['insert_pub_id'];
        
    } else {
        $reprintid=0;
    }
    
    
    $layoutid=$jobinfo['layout_id'];
    if($layoutid!=0)
    {
        $sql="SELECT * FROM layout_sections WHERE layout_id=$layoutid ORDER BY section_number";
        $dbLayout=dbselectmulti($sql);
        
        //get job section information
        $sql="SELECT * FROM jobs_sections WHERE job_id=$jobid";
        $dbJobSections=dbselectsingle($sql);
        $jobSections=$dbJobSections['data'];
        $overrun=0;
        $sinfo=array();
        foreach($dbLayout['data'] as $lay)
        {
            $sinfo[$lay['section_number']]['towers']=explode("|",$lay['towers']);
            $sinfo[$lay['section_number']]['overrun']=$jobSections['data']['section'.$lay['section_number'].'_overrun'];
            if ($jobSections['data']['section'.$lay['section_number'].'_overrun']>0)
            {
                $overrun=$jobSections['data']['section'.$lay['section_number'].'_overrun'];
            }
        }
        
        //calculate plates
        $sql="SELECT * FROM job_plates WHERE job_id=$jobid AND color=0 AND version=1";
        $dbBWplates=dbselectmulti($sql);
        $plates_bw=$dbBWplates['numrows'];
        $sql="SELECT * FROM job_plates WHERE job_id=$jobid AND color=1 AND version=1";
        $dbColorplates=dbselectmulti($sql);
        if ($dbColorplates['numrows']>0)
        {
            foreach($dbColorplates['data'] as $plate)
            {
                $plateid=$plate['id'];
                $sql="SELECT id FROM job_pages WHERE color=1 AND plate_id=$plateid";
                $dbCheckColor=dbselectmulti($sql);
                if($dbCheckColor['numrows']>0)
                {
                    $plates_color+=3;
                }
                $plates_bw+=1;
            }
        }
        
        //calculate pages
        //need to look up section format to convert pages to standard pages
        
        $pages_bw=0;
        $pages_color=0;
        for($i=1;$i<=3;$i++)
        {
            $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND section_code='".$jobSections['section'.$i.'_code']."' AND color=0 AND version=1";
            $dbBWpages=dbselectmulti($sql);
            $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND section_code='".$jobSections['section'.$i.'_code']."' AND color=1 AND version=1";
            $dbColorpages=dbselectmulti($sql);
            $secname="section".$i."_producttype";
            $b=$dbBWpages['numrows'];
            $c=$dbColorpages['numrows'];
            if ($jobSections[$secname]==1 || $jobSections[$secname]==2)
            {
                //tab 
                if (($b2/2)<1)
                {
                    $pages_bw+=0;   
                } else {
                    $pages_bw+=ceil($b/2);
                
                }
                if (($c2/2)<1)
                {
                    $pages_color+=0;   
                } else {
                    $pages_color+=ceil($c/2);
                }
                
            }elseif($jobSections['section'.$i.'_producttype']==3)
            {
               //flexi
               if (($b/4)<0)
               {
                   $pages_bw+=0;
               } else {
                   $pages_bw+=ceil($b/4);
               }
                if (($c/4)<0)
               {
                   $pages_color+=0;
               } else {
                   $pages_color+=ceil($c/4);
               }
                
            }else{
                //broadsheet
                $pages_bw+=$b;
                $pages_color+=$c;
            }
            if (!$GLOBALS['treatGateFoldasFull'])
            {
                if ($jobSections['section'.$i.'_gatefold']){$pages_color--;}   
            }
            
        }
        $totalpages=$pages_bw+$pages_color;
        $totalimpressions=$totalpages*$gross;
        $manhours=($pressmancount*$runningtime)/60;
        if ($totaltons!=0)
        {
            $hoursperton=round($manhours/$totaltons,2);
        } else {
            $hoursperton=0;
        }
        if ($manhours!=0)
        {
            $impressionsperhour=round($impressionsperhour/$manhours);
        } else {
            $impressionsperhour=0;
        }
        
        //now get plate and page times
        $sql="SELECT * FROM job_plates WHERE job_id=$jobid ORDER BY black_receive DESC LIMIT 1";
        $dbLastPlate=dbselectsingle($sql);
        $lastPlate=$dbLastPlate['data']['black_receive'];
        if ($lastPlate!=''){$lastPlate=", last_plate='$lastPlate'";}else{$lastPlate='';}
        
        $sql="SELECT * FROM job_pages WHERE job_id=$jobid ORDER BY page_release DESC LIMIT 1";
        $dbLastPage=dbselectsingle($sql);
        $lastPage=$dbLastPage['data']['page_release'];
        if ($lastPage!=''){$lastPage=", last_page='$lastPage'";}else{$lastPage='';}
        
        $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND color=1 ORDER BY color_release DESC LIMIT 1";
        $dbLastColor=dbselectsingle($sql);
        $lastColor=$dbLastColor['data']['color_release'];
        if ($lastColor!=''){$lastColor=", last_color='$lastColor'";}else{$lastColor='';}
        
        
        //last thing before saving
        //if this is a reprint job we want to make sure to reset the job id to
        //the reprint id, otherwise we're going to mess with the original data
        //so....
        if ($reprintid!=0)
        {
            $jobid=$reprintid;
        }
        
        //updating an existing stat file
            $sql="UPDATE job_stats SET folder='$folder', startdatetime_goal='$schedstart',
             startdatetime_actual='$actualstart', stopdatetime_goal='$schedstop', 
             stopdatetime_actual='$actualstop', run_time='$runningtime', run_speed='$runspeed', 
             good_runspeed='$goodrunspeed', counter_start='$counterstart',  
             counter_stop='$counterstop', spoils_startup='$spoilsstartup', gross='$gross', 
             spoils_running='$spoilsrunning', spoils_total='$spoilstotal', draw='$draw', 
             goodcopy_actual='$goodtime', total_downtime='$downtime', waste_percent='$wastepercent',  
           plates_bw='$plates_bw', plates_color='$plates_color', start_offset='$startoffset', 
            finish_offset='$finishoffset', sched_runtime='$schedruntime', pages_color='$pages_color',
            pages_bw='$pages_bw', $lastPlateUpdate $lastPageUpdate $lastColorUpdate man_hours='$manhours', total_tons='$totaltons', hours_per_ton='$hoursperton', impressions_per_hour='$impressionsperhour',  tower_info='$towerinfo'
             WHERE id=$statsid";
            $dbUpdate=dbexecutequery($sql);
            $error.=$dbUpdate['error'];
            
        $datatime=date("Y-m-d H:i:s");
        $databy=$_SESSION['cmsuser']['userid'];
       
        $jobsql="UPDATE jobs SET data_collected=1, dataset_time='$datatime', dataset_by='$databy', notes_press='$notes', stats_id='$statsid' WHERE id=$jobid";
        $dbJobUpdate=dbexecutequery($jobsql);
        $error.=$dbJobUpdate['error'];
        
        
        //lets see if this was a job that should have converted to a insert, but wasn't due to the vagaries of 
        //the recurring job system
        //look up the run to find out if it is a "run inserts" job
        $sql="SELECT * FROM publications_runs WHERE id=$runid";
        $dbRun=dbselectsingle($sql);
        $run=$dbRun['data'];
        if($run['run_inserts'])
        {
            //yes, it's supposed to be an insert as well, lets look for an insert with this job id as weprint_id
            $sql="SELECT * FROM inserts WHERE weprint_id=$jobid";
            $dbCheck=dbselectsingle($sql);
            if($dbCheck['numrows']==0)
            {
                //oops, it hasn't been created, we need to do that now
                printJob2Inserter($pubid,$runid,$pubdate,$draw,$jobid,$totalpages,$insertpubid);
            }
        }
        
        
        // update any existing insert tied to this job with a receive=1 and a receive_count= total produced
        $sql="UPDATE inserts SET received=1, receive_count='$draw', receive_by='0', receive_date='".date("Y-m-d")."', receive_datetime='".date("Y-m-d H:i")."' WHERE weprint_id='$jobid";
        $dbInsertUpdate=dbexecutequery($sql);
        $error.=$dbInsertUpdate['error'];
        
        
    }
 }    

function pressWearTear($jobid,$debug=false)
{
    $sql="SELECT * FROM jobs WHERE id=$jobid";
    $dbJob=dbselectsingle($sql);
    $jobinfo=$dbJob['data'];
    
    $layoutid=$jobinfo['layout_id'];
    $pagewidth=$jobinfo['pagewidth'];
    $jobid=$jobinfo['id'];
    
    $sql="SELECT * FROM job_stats WHERE job_id=$jobid";
    $dbStats=dbselectsingle($sql);
    $stats=$dbStats['data'];
    
    if($debug)
    {
        print "Job & stat details<br/><pre>";
        print_r($jobinfo);
        print "<br />";
        print_r($stats);
        print "</pre>\n";
    }
    
    $schedstart=$jobinfo['startdatetime'];
    $schedstop=$jobinfo['enddatetime'];
    $draw=$jobinfo['draw'];     
    $pubid=$jobinfo['pub_id'];
    $runid=$jobinfo['run_id'];
    $pubdate=$jobinfo['pub_date'];
    $folder=$jobinfo['folder'];
    $goodtime=$stats['goodcopy_actual'];
    $actualstart=$stats['startdatetime_actual'];
    $actualstop=$stats['stopdatetime_actual'];
    //math for run length calculation
    //get minutes between actual stop and start
    $start=strtotime($actualstart);
    $stop=strtotime($actualstop);
    
    $startoffset=round(($start-strtotime($schedstart))/60,2);
    $finishoffset=round(($stop-strtotime($schedstop))/60,2);
    $schedruntime=round((strtotime($schedstop)-strtotime($schedstart))/60,2);
    $printdate=date("Y-m-d",strtotime($startD));
    $downtime=$stats['total_downtime'];
    $runningtime=round(($stop-$start)/60,2); //should give us running time in decimal minutes
    $goodrunningtime=$runningtime-$downtime;
    
    $counterstart=$stats['counter_start'];
    $counterstop=$stats['counter_stop'];
    $spoilsstartup=$stats['spoils_startup'];
    
    //calculations for waste, speeds, spoils
    $gross=$counterstop-$counterstart;
    $spoilstotal=$gross-$draw;
    $spoilsrunning=$spoilstotal-$spoilsstartup;
    if($draw>0)
    {
        $wastepercent=round($spoilstotal/$draw*100,2);
    } else {
        $wastepercent=0;
    }
    if($runningtime>0)
    {
        $runspeed=round($gross/($runningtime/60),0);
    } else {
        $runspeed=0;
    }
    if($goodrunningtime>0)
    {
        $goodrunspeed=round($gross/($goodrunningtime/60),0);
    } else {
        $goodrunspeed=0;
    }
    $pressid=$jobinfo['press_id'];
    
    $sql="SELECT * FROM job_paper WHERE job_id='$jobid'";
    $dbPaper=dbselectmulti($sql);
    if($dbPaper['numrows']>0)
    {
        //update the folder and ribbon decks used for this job
        $lsql="SELECT * FROM layout WHERE id=$layoutid";
        $dbLayout=dbselectsingle($sql);
        if ($dbLayout['data']['ribbon1_used']){
            $sql="UPDATE press_towers SET impressions=impressions+$gross, running_time=running_time+".round($runningtime)." WHERE press_id='$pressid' AND tower_name='Ribbon Deck 1'";
            if($debug)
            {
                print "Updating Ribbon Deck 1 with:<br />$sql<br />";
            }
            $dbUpdateRibbon=dbexecutequery($sql);
            $error.=$dbUpdateRibbon['error'];
        }
        if ($dbLayout['data']['ribbon2_used']){
            $sql="UPDATE press_towers SET impressions=impressions+$gross, running_time=running_time+".round($runningtime)." WHERE press_id='$pressid' AND tower_name='Ribbon Deck 2'";
            $dbUpdateRibbon=dbexecutequery($sql);
            $error.=$dbUpdateRibbon['error'];
            if($debug)
            {
                print "Updating Ribbon Deck 2 with:<br />$sql<br />";
            }
            
        }
            
        //grab towers from stats tower_info
        $towers=explode("|",$stats['tower_info']);
        foreach($towers as $ptower)
        {
            $ptower=explode(",",$ptower);
            $towerid=$ptower[0];
            
            //update the towers themselves
            $sql="UPDATE press_towers SET impressions=impressions+$gross, 
            running_time=running_time+".round($runningtime)." WHERE id=$towerid";
            $dbTowerUpdate=dbexecutequery($sql);
            if($debug)
            {
                print "Updating Press Tower id=$towerid with:<br />$sql<br />";
            }
            
            //update all part instances
            $sql="UPDATE part_instances SET cur_time=cur_time+".round($runningtime).", 
            cur_count=cur_count+$gross WHERE equipment_id=$pressid AND component_id=$towerid 
            AND equipment_type='printing' AND replaced=0";
            $dbTowerPartUpdate=dbexecutequery($sql);
            $error.=$dbTowerPartUpdate['error'];
            if($debug)
            {
                print "Updating Part Instances for towerid=$towerid & equipment id=$pressid:<br />$sql<br />";
            }
            
            //update all PM instances
            $sql="UPDATE pm_instances SET cur_time=cur_time+".round($runningtime).", 
            cur_count=cur_count+$gross WHERE equipment_id=$pressid AND component_id=$towerid 
            AND equipment_type='printing' AND replaced=0";
            $dbTowerPartUpdate=dbexecutequery($sql);
            $error.=$dbTowerPartUpdate['error'];
            if($debug)
            {
                print "Updating PM Tasks for towerid=$towerid & equipment id=$pressid:<br />$sql<br />";
            }
            
            //update any equipment "owned" by those towers
            $sql="SELECT * FROM press_towers WHERE id=$towerid";
            $dbTower=dbselectsingle($sql);
            $tower=$dbTower['data'];
            $stackers=explode("|",$tower['stackers']);
            $strappers=explode("|",$tower['strappers']);
            $splicers=explode("|",$tower['splicers']);
            $counterveyors=explode("|",$tower['counterveyors']);
            if(count($stackers)>0)
            {
                foreach($stackers as $key=>$tid)
                {
                    if($tid!='')
                    {
                        $sql="UPDATE part_instances SET cur_time=cur_time+".round($runningtime).", 
                        cur_count=cur_count+$gross WHERE equipment_id=$tid AND equipment_type='generic' 
                        AND replaced=0";
                        $dbTowerPartUpdate=dbexecutequery($sql);
                        $error.=$dbTowerPartUpdate['error'];
                        $sql="UPDATE pm_instances SET cur_time=cur_time+".round($runningtime).", 
                        cur_count=cur_count+$gross WHERE equipment_id=$tid AND equipment_type='generic' 
                        AND replaced=0";
                        $dbTowerPartUpdate=dbexecutequery($sql);
                        $error.=$dbTowerPartUpdate['error'];
                        if($debug)
                        {
                            print "Updating Stakcer for towerid=$towerid & equipment id=$pressid:<br />$sql<br />";
                        }
                    }    
                }    
            }
            if(count($strappers)>0)
            {
                foreach($strappers as $key=>$tid)
                {
                    if($tid!='')
                    {
                        $sql="UPDATE part_instances SET cur_time=cur_time+".round($runningtime).", 
                        cur_count=cur_count+$gross WHERE equipment_id=$tid AND equipment_type='generic' 
                        AND replaced=0";
                        $dbTowerPartUpdate=dbexecutequery($sql);
                        $error.=$dbTowerPartUpdate['error'];
                        $sql="UPDATE pm_instances SET cur_time=cur_time+".round($runningtime).", 
                        cur_count=cur_count+$gross WHERE equipment_id=$tid AND equipment_type='generic' 
                        AND replaced=0";
                        $dbTowerPartUpdate=dbexecutequery($sql);
                        $error.=$dbTowerPartUpdate['error'];
                         if($debug)
                        {
                            print "Updating Strapper for towerid=$towerid & equipment id=$pressid:<br />$sql<br />";
                        }
                    }    
                }    
            }
            if(count($splicers)>0)
            {
                foreach($splicers as $key=>$tid)
                {
                    if($tid!='')
                    {
                        $sql="UPDATE part_instances SET cur_time=cur_time+".round($runningtime).", 
                        cur_count=cur_count+$gross WHERE equipment_id=$tid AND equipment_type='generic' 
                        AND replaced=0";
                        $dbTowerPartUpdate=dbexecutequery($sql);
                        $error.=$dbTowerPartUpdate['error'];
                        $sql="UPDATE pm_instances SET cur_time=cur_time+".round($runningtime).", 
                        cur_count=cur_count+$gross WHERE equipment_id=$tid AND equipment_type='generic' 
                        AND replaced=0";
                        $dbTowerPartUpdate=dbexecutequery($sql);
                        $error.=$dbTowerPartUpdate['error'];
                        if($debug)
                        {
                            print "Updating Splicer for towerid=$towerid & equipment id=$pressid:<br />$sql<br />";
                        }
                    }    
                }    
            }
            
            if(count($counterveyors)>0)
            {
                foreach($counterveyors as $key=>$tid)
                {
                    if($tid!='')
                    {
                        $sql="UPDATE part_instances SET cur_time=cur_time+".round($runningtime).", 
                        cur_count=cur_count+$gross WHERE equipment_id=$tid AND equipment_type='generic' 
                        AND replaced=0";
                        $dbTowerPartUpdate=dbexecutequery($sql);
                        $error.=$dbTowerPartUpdate['error'];
                        $sql="UPDATE pm_instances SET cur_time=cur_time+".round($runningtime).", 
                        cur_count=cur_count+$gross WHERE equipment_id=$tid AND equipment_type='generic' 
                        AND replaced=0";
                        $dbTowerPartUpdate=dbexecutequery($sql);
                        $error.=$dbTowerPartUpdate['error'];
                 
                    }    
                }    
            }
            
        }
        
        foreach($dbPaper['data'] as $jpaper)
        {
            
            $sql="SELECT * FROM paper_types WHERE id=$jpaper[papertype_id]";
            $dbPapertype=dbselectsingle($sql);
            $papertype=$dbPapertype['data'];
            $pricePerTon=$papertype['price_per_ton'];
            $pagelength=$GLOBALS['broadsheetPageHeight'];
            
            $paperdataid=$papertype['paperdataid'];
            $pagesonroll=round($rollwidth/$pagewidth,0);
            
            
            //convert gsm to basisweight
            $basisweight=gsmToBasisweight($papertype['paper_weight'],$paperdataid);
            //get pages per pound
            $factor=newsprintPagesPerPound($basisweight,$pagewidth,$pagelength,$paperdataid);
            $factor=round($factor,5);
            //calculate tonnage
            //pages on roll * gross / factor should give us tonnage
            $tonnage=round($pagesonroll*$gross/$factor,2); //is in pounds right now
            //convert $tonnage to MT
            $tonnage=round(poundsToKilograms($tonnage)/1000,2); //should be in MT now
            $totaltons+=$tonnage;
            $cost=round($tonnage*$pricePerTon,2);
            $sql="UPDATE job_paper SET price_per_ton='$pricePerTon', factor='$factor', 
            calculated_tonnage='$tonnage', calculated_cost='$cost' WHERE id=$jpaper[id]";
            $dbPaper=dbexecutequery($sql);
            if($debug)
            {
                print "Updating Paper usage for towerid=$towerid & equipment id=$pressid:<br />$sql<br />";
            }
        }
    }
} 
 
function checkTextAlerts($jobid,$action)
{
    $sql="SELECT A.stats_id,A.pub_id,A.run_id,B.pub_name,C.run_name FROM jobs A, publications B, publications_runs C WHERE A.id=$jobid AND A.pub_id=B.id AND A.run_id=C.id";
    $dbJobinfo=dbselectsingle($sql);
    $pubid=$dbJobinfo['data']['pub_id'];    
    $runid=$dbJobinfo['data']['run_id'];
    $pubname=$dbJobinfo['data']['pub_name'];
    $runname=$dbJobinfo['data']['run_name'];
    $statsid=$dbJobinfo['data']['stats_id'];
    $sql="SELECT * FROM user_textalerts WHERE run_id=$runid AND pub_id=$pubid";
    $dbUsers=dbselectmulti($sql);
    if ($dbUsers['numrows']>0)
    {
        //get start stop times
        $sql="SELECT * FROM job_stats WHERE id=$statsid";
        $dbStats=dbselectsingle($sql);
        $stats=$dbStats['data'];
        $starttime=date("H:i",strtotime($stats['startdatetime_actual']));
        $stoptime=date("H:i",strtotime($stats['stopdatetime_actual']));
        if ($action=='start')
        {
            $message="$pubname - $runname started at $starttime"; 
        } else {
            $message="$pubname - $runname finished at $stoptime";
        } 
        foreach($dbUsers['data'] as $user)
        {
            sendTextAlert($user['user_id'],$message);    
        }
    }    
 }  



function sendTextAlert($userid,$message)
{
    $sql="SELECT cell,carrier FROM users WHERE id=$userid";
    $dbCarrier=dbselectsingle($sql);
    $carrier=$dbCarrier['data']['carrier'];
    if ($carrier=='')
    {
        //do nothing
    } else {
        $cell=$dbCarrier['data']['cell'];
        switch ($carrier)
        {
            case "nextel":
            $formatted_number = $cell."@messaging.nextel.com";
            break;
            
            case "virgin":
            $formatted_number = $cell."@vmobl.com";
            break;
            
            case "cingular":
            $formatted_number = $cell."@cingularme.com";
            break;
            
            case "att":
            $formatted_number = $cell."@txt.att.net";
            break;
            
            case "sprint":
            $formatted_number = $cell."@messaging.sprintpcs.com";
            break;
            
            case "tmobile":
            $formatted_number = $cell."@tmomail.net";
            break;
            
            case "verizon":
            $formatted_number = $cell."@vtext.com";
            break;
            
            case "cricket":
            $formatted_number = $cell."@sms.mycricket.com";
            break;
            
        }
        mail("$formatted_number", "Production Update", "$message","From: ".$GLOBALS['systemEmailFromAddress']);
    }
}


function batch_geocode($addresses,$defaultLat=0,$defaultLon=0,$display=false,$updatefield='')
{
    $badads=array();
    $delay = 0;
    $base_url = "http://maps.google.com/maps/geo";
    $out="&output=csv&key=$key&sensor=false";
    //lets test a hardcoded address:
    if($display && $updatefield==''){print "Batch geocoder was handed ".count($addresses)." addresses to check...<br>";}
    // Iterate through the rows, geocoding each address
    $i=0;
    $l=1; 
    foreach ($addresses as $adid=>$caddress)
    {
      $geocode_pending = true;
      while ($geocode_pending) {
        $address = urlencode($caddress["street"].' '.$caddress['city'].' '.$caddress['state'].' '.$caddress['zip']);
        $url = $base_url . "?q=" .$address.$out;
        //$csv = file_get_contents($url);
        $temp=get_web_page($url);
        $csv=$temp['content'];

        //print "CSV RESULT: <a href='$url' target='_blank'>$url</a> -- $csv<br />\n";
        //print "Attempting to geocode $address with url of $url<br>->Result is ";
        $csvSplit=explode(",",$csv);
        $status=$csvSplit[0];
        $accuracy=$csvSplit[1];
        $lat=$csvSplit[2];
        $lon=$csvSplit[3];
        if($display)
        {
            if($updatefield!='' && $l==25)
            {
                print "<script>\$('#$updatefield').html('Checking #$i $caddress[street]<br>Result from web call was $csv with a status of $status')</script>";
                for($k = 0; $k < 320000; $k++)echo ' ';
                $l=1;
            } else if($updatefield==''){
                print "Checking $caddress[street] with url of $url<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Result from web call was $csv with a status of $status<br>";
            }
            $l++;
        }
        if ($status=="200") {
          // successful geocode
          $geocode_pending = false;
          $lat = $csvSplit[2];
          $lng = $csvSplit[3];
          if ($lat=='' || $lat==0){$lat=$defaultLat;}
          if ($lon=='' || $lon==0){$lon=$defaultLon;}
          
          if($display && $updatefield==''){print "Geocoded $adid successfully<br />";}
          $addresses[$adid]['status']='success';
          $addresses[$adid]['lat']=$lat;
          $addresses[$adid]['lon']=$lon;
          $delay=0;
        } else if ($status=="620") {
          // sent geocodes too fast
          $delay = 100000;
        } else {
          // failure to geocode
          $geocode_pending = false;
          $addresses[$adid]['status']='fail';
          $addresses[$adid]['lat']=0;
          $addresses[$adid]['lon']=0;
        }
        usleep($delay);
        //print "$status with lat=$lat and lon=$lng<br>";
      }
      $i++;
    }
   
   return $addresses;
}

function get_web_page( $url )
{
    
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
    );

    $ch      = curl_init( $url );
    
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    //     // return web page
    //    CURLOPT_HEADER         => false,    // don't return headers
    //    CURLOPT_FOLLOWLOCATION => true,     // follow redirects
    //    CURLOPT_ENCODING       => "",       // handle all encodings
    //    CURLOPT_USERAGENT      => "spider", // who am i
    //    CURLOPT_AUTOREFERER    => true,     // set referer on redirect
    //    CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
    //    CURLOPT_TIMEOUT        => 120,      // timeout on response
    //    CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects)
   
   //curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}



/**************************************************************************************
* THIS SECTION IS FOR INSERT RELATED FUNCTIONS LIKE ADDING AN INSERT, REMOVING AN INSERT
* GENERATING THE STATION MARKUP FOR A PACKAGE
* AND GETTING INSERT QTIP DATA FOR PACKAGING AND THE INSERT CALENDAR
*/

function getPackageContents($qtip,$packageid)
{
    $sql="SELECT package_name FROM jobs_inserter_packages WHERE id=$packageid";
    $dbPackage=dbselectsingle($sql);
    $qtip.="<br><b>Package: </b>".$dbPackage['data']['package_name'];
    
    $sql="SELECT * FROM jobs_packages_inserts WHERE package_id=$packageid ORDER BY insert_type";
    $dbContents=dbselectmulti($sql);
    if($dbContents['numrows']>0)
    {
        $qtip.=" contains the following<br>";
                   
        foreach($dbContents['data'] as $item)
        {
            if($item['insert_type']=='package')
            {
                $qtip.=getPackageContents($qtip,$item['insert_id']);  
                
            } elseif($item['insert_type']=='jacket')
            {
                $qtip.="<b>Generic jacket</b><br>";
            } else {
                //look up the insert details
                $sql="SELECT A.*, B.account_name FROM inserts A, accounts B WHERE A.id=$item[insert_id] AND A.advertiser_id=B.id";
                $dbInsert=dbselectsingle($sql);
                $insert=$dbInsert['data'];
                $qtip.="<b>".stripslashes($insert['account_name'])."</b><br>&nbsp;&nbsp;";
                $qtip.="<b>Tagline: </b>".stripslashes($insert['insert_tagline'])."<br>&nbsp;&nbsp;";
                $qtip.="<b>Request: </b>".stripslashes($insert['insert_quantity'])."<br>&nbsp;&nbsp;";
                $qtip.="<b>Tab Pages: </b>".stripslashes($insert['tab_pages'])."<br>&nbsp;&nbsp;";
                $qtip.="<b>Status: </b>";
                if($insert['confirmed'])
                {
                    $qtip.="Confirmed, "; 
                } else {
                    $qtip.="Not confirmed, ";
                }
                if($insert['received'])
                {
                    $qtip.="Received<br>"; 
                } else {
                    $qtip.="Not received<br>";
                }
                if($insert['weprint_id']>0)
                {
                    $qtip.="&nbsp;&nbsp;<b>We printed this job</b>";
                }
            }
        }
    } else {
        $qtip.=" contains no inserts<br>";
    }
    return $qtip;
}


function addInsert($planid,$packid,$insertid,$inserttype,$stationid,$inserterid)
{
    global $response;
    //store the record in jobs_packages_inserts
    $sql="INSERT INTO jobs_packages_inserts (plan_id, package_id, insert_id, inserter_id, hopper_id, insert_type) 
    VALUES ('$planid', '$packid', '$insertid', '$inserterid', '$stationid', '$inserttype')";
    $dbInsert=dbinsertquery($sql);
    if($dbInsert['error']=='')
    {
        //now a couple quick calls to get totals
        $sql="SELECT * FROM jobs_inserter_packages WHERE id='$packid'";
        $dbPackageData=dbselectsingle($sql);
        $packagedata=$dbPackageData['data'];
        
        $packageweight=$packagedata['total_weight'];
        $packageinserts=$packagedata['total_inserts'];
        $packagepages=$packagedata['tab_pages'];
        $response['package_id']=$packid;
            
        if($inserttype=='insert')
        {
            $sql="SELECT * FROM inserts WHERE id=$insertid";
            $dbInsertData=dbselectsingle($sql);
            $insertdata=$dbInsertData['data'];
            $weight=$insertdata['piece_weight'];
            $tabpages=$insertdata['tab_pages'];
            $count=1; 
        } elseif($inserttype=='package')
        {
            $sql="SELECT * FROM jobs_inserter_packages WHERE id='$insertid'"; //since the insert id in this case will be the package id
            $dbAddedPackage=dbselectsingle($sql);
            $addedPackageData=$dbAddedPackage['data'];
            $weight=$addedPackageData['total_weight'];
            //$count=$addedPackageData['total_inserts'];
            $count=1;  //an insert should only count as one additional insert since it only takes up one station
            $tabpages=$addedPackageData['tab_pages'];
        } else {
            $weight=1;
            $tabpages=2; //single broadsheet for jacket equals 2 tab pages
            $count=1;
        }
        $response['item_weight']=$weight;
        $response['item_pages']=$tabpages;
        $response['item_count']=$count;
        
        //add it
        $packageweight+=$weight;
        $packageinserts+=$count;
        $packagepages+=$tabpages;
        //update the record
        $sql="UPDATE jobs_inserter_packages SET total_weight='$packageweight', total_inserts='$packageinserts', 
        tab_pages='$packagepages' WHERE id=$packid";
        $dbUpdate=dbexecutequery($sql);
        if($dbUpdate['error']=='')
        {
            /*
            one other thing. when we update the page counts in this package, if it is included in another package
            then we need to update that packages data as well, and also update the on screen data
            so, lets see if this package is contained in another package 
            */
            $response['status']='success';
            $sql="SELECT * FROM jobs_packages_inserts WHERE insert_type='package' AND insert_id='$packid'";
            $dbCheck=dbselectsingle($sql);
            if($dbCheck['numrows']>0)
            {
                //yep, it's contained in another package. Now we need to update that packages totals, and pass them back as well.
                $secpackageid=$dbCheck['data']['package_id'];
                $response['secondary_id']=$secpackageid;
                //do the lookup
                $sql="SELECT * FROM jobs_inserter_packages WHERE id=$secpackageid";
                $dbSecPackage=dbselectsingle($sql);
                $secPackageData=$dbSecPackage['data'];
                $secweight=$secPackageData['total_weight']+$weight; //add this package weight
                $seccount=$secPackageData['total_inserts']; //count doesn't change in second package since a package counts as just one insert
                $sectabpages=$secPackageData['tab_pages']+$tabpages;  //add this package page count
                //now update the record
                $sql="UPDATE jobs_inserter_packages SET total_weight='$secweight', total_inserts='$seccount', 
                tab_pages='$sectabpages' WHERE id=$secpackageid";
                $dbUpdate=dbexecutequery($sql);
                if($dbUpdate['error']!='')
                {
                     $response['status']='error';
                     $response['error_message']=$dbUpdate['error'];
                }
                $response['secondary_weight']=$secweight;
                $response['secondary_count']=$seccount;
                $response['secondary_pages']=$sectabpages;
                
                
                /*
                and because we are a bit paranoid, let's check to see if this package is in another package... 
                */
                $sql="SELECT * FROM jobs_packages_inserts WHERE insert_type='package' AND insert_id='$secpackageid'";
                $dbCheck=dbselectsingle($sql);
                if($dbCheck['numrows']>0)
                {
                    //yep, it's contained in another package. Now we need to update that packages totals, and pass them back as well.
                    $thirdpackageid=$dbCheck['data']['package_id'];
                    $response['third_id']=$thirdpackageid;
                    //do the lookup
                    $sql="SELECT * FROM jobs_inserter_packages WHERE id=$thirdpackageid";
                    $dbThirdPackage=dbselectsingle($sql);
                    $thirdPackageData=$dbThirdPackage['data'];
                    $thirdweight=$thirdPackageData['total_weight']+$weight; //add this package weight
                    $thirdcount=$thirdPackageData['total_inserts']; //count doesn't change in third pacakge
                    $thirdtabpages=$thirdPackageData['tab_pages']+$tabpages;  //add this package page count
                    //now update the record
                    $sql="UPDATE jobs_inserter_packages SET total_weight='$thirdweight', total_inserts='$thirdcount', 
                    tab_pages='$thirdtabpages' WHERE id=$thirdpackageid";
                    $dbUpdate=dbexecutequery($sql);
                    if($dbUpdate['error']!='')
                    {
                         $response['status']='error';
                         $response['error_message']=$dbUpdate['error'];
                    }
                    $response['third_weight']=$thirdweight;
                    $response['third_count']=$thirdcount;
                    $response['third_pages']=$thirdtabpages;
                }
                
                
            }
            $response['weight']=$packageweight;
            $response['count']=$packageinserts;
            $response['pages']=$packagepages;
        } else {
            $response['status']='error';
            $response['error_message']=$dbUpdate['error'];
        }
    } else {
        $response['status']='error';
        $response['error_message']=$dbInsert['error'];
    }
    return $response;
}

function removeInsert($planid,$packid,$insertid,$inserttype,$stationid)
{
    global $response;
    
    //get the record first, then delete it
    $sql="SELECT * FROM jobs_packages_inserts WHERE plan_id='$planid' AND package_id='$packid'
     AND insert_id='$insertid' AND insert_type='$inserttype'";
    $dbRecord=dbselectsingle($sql);
    $record=$dbRecord['data'];
    //delete the record in jobs_packages_inserts
    $deletesql="DELETE FROM jobs_packages_inserts WHERE plan_id='$planid' AND package_id='$packid'
     AND insert_id='$insertid' AND insert_type='$inserttype'";
    $dbDelete=dbexecutequery($deletesql);
    $response['deletesql']=$deletesql;
    if($dbDelete['error']=='')
    {
        //now a couple quick calls to get totals
        $sql="SELECT * FROM jobs_inserter_packages WHERE id='$packid'";
        $dbPackageData=dbselectsingle($sql);
        $packagedata=$dbPackageData['data'];
        
        $packageweight=$packagedata['total_weight'];
        $packageinserts=$packagedata['total_inserts'];
        $packagepages=$packagedata['tab_pages'];
        
        if($inserttype=='insert')
        {
            $sql="SELECT * FROM inserts WHERE id=$insertid";
            $dbInsertData=dbselectsingle($sql);
            $insertdata=$dbInsertData['data'];
            $weight=$insertdata['piece_weight'];
            $tabpages=$insertdata['tab_pages'];
            $count=1; 
        } elseif($inserttype=='package')
        {
            $sql="SELECT * FROM jobs_inserter_packages WHERE id='$insertid'"; //since the insert id in this case will be the package id
            $dbAddedPackage=dbselectsingle($sql);
            $addedPackageData=$dbAddedPackage['data'];
            $weight=$addedPackageData['total_weight'];
            //$count=$addedPackageData['total_inserts'];
            $count=1; //a package will count as only 1 insert since it takes up only one station
            $tabpages=$addedPackageData['tab_pages'];
        } else {
            $weight=1;
            $tabpages=2;  //jacket is one broadsheet page, so 2 tab pages
            $count=1;
        }
        $response['item_weight']=$weight;
        $response['item_pages']=$tabpages;
        $response['item_count']=$count;
        //remove that amount from this package
        if($packageweight>0){$packageweight-=$weight;}
        if($packageinserts>0){$packageinserts-=$count;}
        if($packagepages>0){$packagepages-=$tabpages;}
         //update the record
        $sql="UPDATE jobs_inserter_packages SET total_weight='$packageweight', total_inserts='$packageinserts', 
        tab_pages='$packagepages' WHERE id=$packid";
        $dbUpdate=dbexecutequery($sql);
        if($dbUpdate['error']=='')
        {
            /*
            one other thing. when we update the page counts in this package, if it is included in another package
            then we need to update that packages data as well, and also update the on screen data
            so, lets see if this package is contained in another package 
            */
            $response['status']='success';
            $response['package_id']=$packid;
            $sql="SELECT * FROM jobs_packages_inserts WHERE insert_type='package' AND insert_id='$packid'";
            $dbCheck=dbselectsingle($sql);
            if($dbCheck['numrows']>0)
            {
                //yep, it's contained in another package. Now we need to update that packages totals, and pass them back as well.
                $secpackageid=$dbCheck['data']['package_id'];
                $secstationid=$dbCheck['data']['hopper_id'];
                $response['secondary_id']=$secpackageid;
                $secid=$dbCheck['data']['id'];
                //do the lookup
                $sql="SELECT * FROM jobs_inserter_packages WHERE id=$secpackageid";
                $dbSecPackage=dbselectsingle($sql);
                $secPackageData=$dbSecPackage['data'];
                $secweight=$secPackageData['total_weight']-$weight; //remove this package weight
                $seccount=$secPackageData['total_inserts']; //count of inserts doesnt change in second package
                $sectabpages=$secPackageData['tab_pages']-$tabpages;  //remove this package page count
                
                
                //we need to know what station it was in
                $station="pack_".$secpackageid."-station_".$secstationid;
                $response['second_station']=$station;
                
                //remove the package from the secondary -- WHY??? we don't need to do this for a package
                $sql="DELETE FROM jobs_packages_inserts WHERE id=$secid";
                $dbDelete=dbexecutequery($sql);
                $response['secondary_package_delete']=$sql;
                
                //now update the record
                $sql="UPDATE jobs_inserter_packages SET total_weight='$secweight', total_inserts='$seccount', 
                tab_pages='$sectabpages' WHERE id=$secpackageid";
                $dbUpdate=dbexecutequery($sql);
                if($dbUpdate['error']!='')
                {
                     $response['status']='error';
                     $response['error_message']=$dbUpdate['error'];
                }
                $response['secondary_weight']=$secweight;
                $response['secondary_count']=$seccount;
                $response['secondary_pages']=$sectabpages;
                
                /*
                because we are paranoid folk, we also check for a third level package
                */
                $sql="SELECT * FROM jobs_packages_inserts WHERE insert_type='package' AND insert_id='$secpackageid'";
                $dbCheck=dbselectsingle($sql);
                if($dbCheck['numrows']>0)
                {
                    //yep, it's contained in another package. Now we need to update that packages totals, and pass them back as well.
                    $thirdpackageid=$dbCheck['data']['package_id'];
                    $thirdstationid=$dbCheck['data']['hopper_id'];
                    $response['third_id']=$thirdpackageid;
                    $thirdid=$dbCheck['data']['id'];
                    //do the lookup
                    $sql="SELECT * FROM jobs_inserter_packages WHERE id=$thirdpackageid";
                    $dbThirdPackage=dbselectsingle($sql);
                    $thirdPackageData=$dbThirdPackage['data'];
                    $thirdweight=$thirdPackageData['total_weight']-$weight; //remove this package weight
                    $thirdcount=$thirdPackageData['total_inserts']; //count doesn't change in third package
                    $thirdtabpages=$thirdPackageData['tab_pages']-$tabpages;  //remove this package page count
                    
                    //now update the record
                    $sql="UPDATE jobs_inserter_packages SET total_weight='$thirdweight', total_inserts='$thirdcount', 
                    tab_pages='$thirdtabpages' WHERE id=$thirdpackageid";
                    $dbUpdate=dbexecutequery($sql);
                    if($dbUpdate['error']!='')
                    {
                         $response['status']='error';
                         $response['error_message']=$dbUpdate['error'];
                    }
                    $response['third_weight']=$thirdweight;
                    $response['third_count']=$thirdcount;
                    $response['third_pages']=$thirdtabpages;
                }
                
                
            }
            $response['weight']=$packageweight;
            $response['count']=$packageinserts;
            $response['pages']=$packagepages;
        } else {
            $response['status']='error';
            $response['error_message']=$dbUpdate['error'];
        }
    } else {
        $response['status']='error';
        $response['error_message']=$dbDelete['error'];
    }
    return $response;
}

function getInserterHTML($inserterid,$packid)
{
    global $response;
    
    //remove any existing hopper pairing for this package
    $sql="DELETE FROM jobs_packages_hoppper_pairings WHERE package_id='$packid'";
    $dbDelete=dbexecutequery($sql);
    
    $sql="SELECT * FROM inserters WHERE id=$inserterid";
    $dbInserter=dbselectsingle($sql);
    $inserter=$dbInserter['data'];
    $candoubleout=$inserter['can_double_out'];
    $inserterturn=$inserter['inserter_turn'];
    $singleout=false;
    
    $response['can_double_out']=$candoubleout;
    $response['inserter_turn']=$inserterturn;
    
    //later these will need to be set when loading a saved set of packages
    $extraDisplay='block';
    $doubleDisplay='none';
    if($doubleDisplay=='none')
    {
        $stationWidth='180px';
    } else {
        $stationWidth='113px';
    }
    $extraDisplayed=false;
    //get the stations
    $sql="SELECT * FROM inserters_hoppers WHERE inserter_id=$inserterid ORDER BY hopper_number";
    $dbStations=dbselectmulti($sql);
    if($dbStations['numrows']>0)
    {
        //run through them fast to get counts
        $minDoubleHopper=$inserterturn+1; //one more than where the turn is
        $i=0;
        $stations=array();
        foreach($dbStations['data'] as $station)
        {
            if($i==0)
            {
                $minHopper=$station['hopper_number'];
                $i++;
            }
            $stations[$station['hopper_number']]=$station['id'];
            $maxDoubleHopper=$station['hopper_number']; //keep setting in, the last value will be the largest
        }
        
        foreach($dbStations['data'] as $station)
        {
            $stationNumber=$station['hopper_number'];
            //if(($stationNumber<=$inserterturn && $candoubleout) || $singleout)
            if($stationNumber>$inserterturn && $candoubleout && !$extraDisplayed)
            {
                $newinserter.="<div id='extraHoppers_$packid' style='display:$extraDisplay;'>\n";
                $extraDisplayed=true;    
            }
            
            $newinserter.="<div style='width:240px;margin-bottom:2px;'>\n";
                $newinserter.="<div class='doubleouts_$packid' style='display:$doubleDisplay;float:left;width:60px;margin-right:5px;'>\n";
                if($candoubleout && $station['hopper_number']<$minDoubleHopper)
                {
                    $guessHopper=$maxDoubleHopper-intval($station['hopper_number'])+1;
                    
                    //add a record to jobs_packages_hoppers_pairings
                    $sql="INSERT INTO jobs_packages_hopper_pairings (package_id, hopper_id, secondary_id, secondary_value) VALUES 
                    ('$packid', '$station[id]','$stations[guessHopper]','$guessHopper')";
                    $dbInsert=dbinsertquery($sql);
                    
                    $newinserter.="<div id='$station[id]_".$packid."_locked' style='float:left;width:20px;display:block;padding-top:6px;'>\n <img src='/artwork/icons/lock_48.png' alt='Locked' height=14/>\n</div>\n";    
                    $newinserter.="<div id='$station[id]_".$packid."_unlocked' style='float:left;width:20px;display:none;padding-top:6px;'>\n <img src='/artwork/icons/lock_open_48.png' alt='Locked' height=14/>\n</div>\n";    
                    $response['process_attachments']=1;
                    $response['attach_events'][]=array('action'=>'click',
                                                       'id'=>"$station[id]_".$packid."_locked",
                                                       'afunction'=>"toggleLock($station[id],$packid,'open',$minHopper,$inserterturn,$stationNumber)");
                    $response['attach_events'][]=array('action'=>'click',
                                                       'id'=>"$station[id]_".$packid."_unlocked",
                                                       'afunction'=>"toggleLock($station[id],$packid,'close',$minHopper,$inserterturn,$stationNumber)");
                    
                    $newinserter.="<div id='$station[id]_".$packid."_second' style='float:left;width:40px;padding-top:4px;'>\n";
                    $newinserter.="<select id='$station[id]_".$packid."_select' style='float:left;display:none;width:40px;background:none;border:none;font-weight:bold;font-size:18px;'>\n";
                    //build select options
                    $newinserter.="<option value='0'>None</option>\n";
                    for($i=$minDoubleHopper;$i<=$maxDoubleHopper;$i++)
                    {
                        if($i==$guessHopper){$selected='selected';}else{$selected='';}
                        $newinserter.="<option value='$i' $selected>$i</option>\n";
                    }
                    $newinserter.="</select>\n";
                    $newinserter.="<span id='$station[id]_".$packid."_selectvalue' style='float:left;display:inline;width:40px;background:none;border:none;font-weight:bold;font-size:18px;'>$guessHopper</span>\n";
                    
                    $newinserter.="</div>\n";
                    
                    $newinserter.="<div class='clear'></div>\n";
                    
                }
                $newinserter.="</div>\n";
                $newinserter.="<div style='float:left;width:30px;margin-right:3px;text-align:right;font-weight:bold;font-size:18px;padding-top:4px;'>\n";
                if($station['jacket_station'])
                {
                    $newinserter.='J-';
                }
                $newinserter.=$stationNumber;
                $newinserter.="</div>\n";
                
                $insertid='';
                $inserttype='';
                $insertinfo='';
                $insertpages='';
                $insertname='';
                $newinserter."<div id='pack_$packid-station_$station[id]' data-packageid='$packid' data-stationid='$station[id]' data-handler='regular' data-info=\"\" data-id=\"0\" data-type=\"insert\" data-classes=\"insert\" data-name=\"\" data-clone='0' class='station ui-widget ui-widget-content' style='float:left;width:$stationWidth;height:30px;'>\n";
                $newinserter.$insertname;
                $newinserter."</div>\n";
                $newinserter.="<input type='hidden' id='pack_$packid-station_$station[id]-insert_type' value='$inserttype' />\n";
                $newinserter.="<input type='hidden' id='pack_$packid-station_$station[id]-insert_id' value='$insertid' />\n";
                $newinserter.="<input type='hidden' id='pack_$packid-station_$station[id]-insert_info' value='$insertinfo' />\n";
                $newinserter.="<input type='hidden' id='pack_$packid-station_$station[id]-insert_pages' value='$insertpages' />\n";
                $newinserter.="<input type='hidden' id='pack_$packid-station_$station[id]-hopper_number' value='$station[hopper_number]' />\n";
                $newinserter.="<input type='hidden' id='pack_$packid-station_$station[hopper_number]-station_id' value='$station[id]' />\n";
                $newinserter.="<div style='float:left;margin-left:4px;width:20px;padding-top:5px;'>
                  <img src='/artwork/icons/cancel_gray_48.png' width=20 onclick='removeInsert($packid,$station[id],0);' />\n</div>\n";
                $newinserter.="<div class='clear'></div>\n";
                    
            $newinserter.="</div><!--closes the station $station[id] in package $packid -->\n";
                
        }
        if($extraDisplayed)
        {
            $newinserter.="</div><!--closing the extra hopper holder for package $packid-->\n";    
        }
    } else {
        $newinserter.="Inserter is not configured.";
    }
    $newinserter.="<div class='clear'></div>\n";
    return $newinserter;
}

function getPackageSettingsHTML($inserterid,$packageid)
{
    global $response;
     
    //build a list of inserters
    $sql="SELECT * FROM inserters";
    $dbInserters=dbselectmulti($sql);
    $inserters[0]='Please select';
    if($dbInserters['numrows']>0)
    {
        foreach($dbInserters['data'] as $inserter)
        {
            $inserters[$inserter['id']]=stripslashes($inserter['inserter_name']);
        }    
    }
    
    $sql="SELECT * FROM inserters WHERE id=$package[inserter_id]";
    $dbInserter=dbselectsingle($sql);
    $inserter=$dbInserter['data'];
    $candoubleout=$inserter['can_double_out'];
    $inserterturn=$inserter['inserter_turn'];
    $singleout=false;
    
    
    
    $sql="SELECT * FROM jobs_inserter_packages WHERE id=$packageid";
    $dbPackage=dbselectsingle($sql);
    $package=$dbPackage['data'];
    
    //later these will need to be set when loading a saved set of packages
    $extraDisplay='block';
    $doubleDisplay='none';
    if($doubleDisplay=='none')
    {
        $stationWidth='180px';
    } else {
        $stationWidth='113px';
    }
    $extraDisplayed=false;
    
     $html="<div class='ui-widget ui-widget-header' style='width:100%;padding:5px;'>\n";
        $html.="<div style='float:left;width:200px;'><span id='package_$package[id]_name'>".stripslashes($package['package_name'])."</span><input id='package_$package[id]_nameedit' value='".stripslashes($package['package_name'])."' style='display:none;width:200px;' /></div>";
        $html.="<div style='float:right;'><img src='/artwork/icons/gear_48.png' alt='Remove Insert' width=24 onClick='expandSettings($package[id]);' /></div>\n";
        $html.="<div class='clear'></div>\n";
        $html.="<div id='settings_$package[id]' style='width:190px;margin-left:auto;margin-right:auto;display:none;'>\n";
            $html.="Run time: ".make_datetime('package_'.$package['id'].'_dt',date("Y-m-d H:i",strtotime($package['package_startdatetime'])));
             $response['process_attachments']=1;
             $response['attach_events'][]=array('action'=>'direct',
                                               'id'=>"0",
                                               'afunction'=>"\$('#package_".$package['id']."_dt').datetimepicker({ dateFormat: 'yy-mm-dd' })"
                                               );
                   
            $html.="Select Inserter:<br>";
            $html.="<select id='package_$package[id]_inserter'>\n";
            foreach($inserters as $key=>$insertername)
            {
                if($key==$package['inserter_id'])
                {
                    $selected='selected';
                } else {
                    $selected='';
                }
                $html.="<option value='$key' $selected>$insertername</option>\n";
            }
            $html.="</select>\n";
            
            $pubdate=$package['pub_date'];
            $pubid=$package['pub_id'];
            $sql="SELECT B.*, A.insert_quantity, C.account_name FROM inserts_schedule A, inserts B, accounts C 
            WHERE A.insert_id=B.id AND A.pub_id=$pubid AND A.insert_date='$pubdate' AND B.advertiser_id=C.id 
            ORDER BY C.account_name";
            $dbInserts=dbselectmulti($sql);
            if($dbInsert['numrows']>0)
            { 
                $stickynotes[0]='None';
                foreach($dbInserts['data'] as $insert)
                {
                    //each insert holder will be 120px wide
                    if($insert['sticky_note'])
                    {
                        $insertname=stripslashes($insert['account_name'])." ".stripslashes($insert['insert_tagline']);
                        $insertname=str_replace("'","",$insertname);
                        $stickynotes[$insert['id']]=$insertname;
                        
                        /* this is the code from the main initial generated on inserterPlanner.php - shouldn't need this
                        $used='';
                        $insertname=stripslashes($insert['account_name'])." ".stripslashes($insert['insert_tagline']);
                        $insertname=str_replace("'","",$insertname);
                        //for testing
                        if($dbPackages['numrows']>0)
                        {
                            forech($dbPackages['data'] as $package)
                            {
                                if($insert['id']==$package['sticky_note_id'])
                                {
                                    $used='used';
                                }
                            }
                        }
                        $stickynames[]="<span id='sticky$insert[id]' class='stickylabel $used'>$insertname</span>";
                        $stickynotes[$insert['id']]=$insertname;
                        */
                    }
                }
                if($GLOBALS['stickyNoteLocation']=='inserter' && count($stickynotes)>1)
                {
                    $html.="<br>Select sticky note:<br><select id='package_$package[id]_sticky' class='packsticky' style='width:190px;' onchange='checkDuplicateSticky($package[id]);'>\n";
                    foreach($stickynotes as $key=>$sticky)
                    {
                        if($key==$package['sticky_note_id'])
                        {
                            $selected='selected';
                        } else {
                            $selected='';
                        }
                        $html.="<option value='$key' $selected >$sticky</option>\n";
                    }
                    $html.="</select>\n";
                }
            }            
            $html.="<br>Production draw request:<br><input type='text' id='package_$package[id]_request' style='width:184px;' value='$package[inserter_request]'/>\n";
            if($candoubleout)
            {
                $toggleDoubleCheck='inline';
            } else {
                $toggleDoubleCheck='none';
            }
            $html.="<br><span id='package_$package[id]_doubleout_display' style='display:$toggleDoubleCheck'><input type=checkbox id='package_$package[id]_doubleout' name='package_$package[id]_doubleout'/>
            <label for='package_$package[id]_doubleout'> Double-out package</label></span><br>\n";
            
            $html.="<input type='button' value='Delete' class='delete' onClick='deletePackage($package[id]);' />";
            $html.="<input type='button' value='Save' onClick='saveSettings($package[id]);' style='float:right;'/>";
            $html.="<input type='hidden' id='package_$package[id]_originalinserter' value='$package[inserter_id]' />\n";
            $html.="<input type='hidden' id='package_$package[id]_originaldoubleout' value='$package[double_out]' />\n";
            $html.="<input type='hidden' id='package_$package[id]_originalrequest' value='$package[inserter_request]' />\n";
            $html.="<input type='hidden' id='package_$package[id]_open' value='false' />\n";    
        $html.="</div><!-- closes settings panel for package $package[id] -->\n";
    $html.="<div class='clear'></div>\n";
    $html.="</div><!-- closing the header area for package $package[id]-->\n";
    //add a package stats area
    $html.="<div class='ui-widget ui-widget-content' style='width:100%;padding:5px;margin-bottom:2px;'>\n";
    $html.="Page count: <span id='package_$package[id]_pagecount'>$package[tab_pages]</span><br>\n";
    $html.="# Inserts: <span id='package_$package[id]_insertcount'>$package[total_inserts]</span><br>\n";
    $html.="Package Weight: <span id='package_$package[id]_weight'>$package[total_weight]</span><br>\n";
    $html.="</div><!--closing the package stats area -->\n";
                
    return $html;        
}

function getInsertToolTip($insertid)
{
    $sql="SELECT A.*, B.account_name FROM inserts A, accounts B WHERE A.id=$insertid AND A.advertiser_id=B.id";
    $dbInsert=dbselectsingle($sql);
    $insert=$dbInsert['data'];
    $qtip.="<b>".stripslashes($insert['account_name'])."</b><br>&nbsp;&nbsp;";
    $qtip.="<b>Tagline: </b>".stripslashes($insert['insert_tagline'])."<br>&nbsp;&nbsp;";
    $qtip.="<b>Request: </b>".stripslashes($insert['insert_quantity'])."<br>&nbsp;&nbsp;";
    $qtip.="<b>Tab Pages: </b>".stripslashes($insert['tab_pages'])."<br>&nbsp;&nbsp;";
    $qtip.="<b>Status: </b>";
    if($insert['confirmed'])
    {
        $qtip.="Confirmed, "; 
    } else {
        $qtip.="Not confirmed, ";
    }
    if($insert['received'])
    {
        $qtip.="Received<br>"; 
    } else {
        $qtip.="Not received<br>";
    }
    if($insert['weprint_id']>0)
    {
        $qtip.="&nbsp;&nbsp;<b>We printed this job</b>";
    }
    //now get zoning details
    //need to first get the schedule id, then work to the insert_zoning table with it
    $sql="SELECT * FROM jobs_inserter_plans WHERE id=$planid";
    $dbPlan=dbselectsingle($sql);
    $pubid=$dbPlan['data']['pub_id'];
    $pubdate=$dbPlan['data']['pub_date'];
    
    $sql="SELECT * FROM inserts_schedule WHERE insert_id='$insertid' AND pub_id=$pubid AND insert_date='$pubdate'";
    $dbSchedule=dbselectsingle($sql);
    $scheduleid=$dbSchedule['data']['id'];
    //$qtip.="Trying schedule lookup with $sql<br>";
    
    $sql="SELECT A.zone_name, B.zone_count FROM publications_insertzones A, insert_zoning B 
    WHERE B.sched_id=$scheduleid AND B.insert_id=$insertid AND B.zone_id=A.id";
    //$qtip.="Trying zone lookup with $sql<br>";
    $dbZones=dbselectmulti($sql);
    if($dbZones['data']>0)
    {
        $qtip.="<b>Zones:</b><br>";
        foreach($dbZones['data'] as $zone)
        {
            $qtip.="&nbsp;&nbsp;".$zone['zone_name']." needs ".$zone['zone_count']."<br>";
        }
    }
    return $qtip;
} 


function inserterWearTear($packageid)
{
    //stations is an array of all stations actively used
    //jobs_inserter_rundata and jobs_inserter_rundata_stations hold all the information we need
    
    //also need some information from the package
    $sql="SELECT * FROM jobs_inserter_packages WHERE id=$packageid";
    $dbPackage=dbselectsingle($sql);
    $package=$dbPackage['data'];
    $inserterid=$package['inserter_id'];
    
    $sql="SELECT * FROM jobs_inserter_rundata WHERE package_id='$packageid'";
    $dbRunData=dbselectsingle($sql);
    $runData=$dbRunData['data'];
    
    $runningtime=$runData['run_length'];
    $calctotalpieces=$runData['calc_total_pieces'];
    //lets only do this if wear has not been applied
    if(!$runData['wear_applied'])
    {
        //grab all those stations used in this package with an insert quantity>0
        $sql="SELECT * FROM jobs_inserter_rundata_stations WHERE package_id='$packageid' AND quantity>0";
        $dbStationData=dbselectmulti($sql);
        
        $otherequipment=explode("|",$runData['other_equipment']);
        
        //lets apply the proper amount of wear for each station
        if($dbStationData['numrows']>0)
        {
            foreach($dbStationData['data'] as $station)
            {
                //update the parts
                $sql="UPDATE part_instances SET cur_time=cur_time+$runningtime, cur_count=cur_count+$station[quantity] 
                WHERE equipment_id='$inserterid' AND component_id='$station[station_id]' AND replaced=0";
                $dbUpdatePart=dbexecutequery($sql);
                $error.=$dbUpdatePart['error'];
                //update the PM tasks
                $sql="UPDATE pm_instances SET cur_time=cur_time+$runningtime, cur_count=cur_count+$station[quantity] 
                WHERE equipment_id='$inserterid' AND component_id='$station[station_id]' AND completed=0";
                $dbUpdatePart=dbexecutequery($sql);
                $error.=$dbUpdatePart['error'];
                
                //update the station itself
                $sql="UPDATE inserters_hoppers SET running_cycles=running_cycles+$station[quantity], 
                running_time=running_time+$runningtime WHERE id=$station[station_id]";
                $dbUpdateStations=dbexecutequery($sql);
                $error.=$dbUpdateStations['error'];
                     
            }
        }
        
        //now, lets update any other equipment
        if(count($otherequipment)>0)
        {
            foreach($otherequipment as $key=>$id)
            {
                if($id!='')
                {
                    //update the parts
                    $sql="UPDATE part_instances SET cur_time=cur_time+$runningtime, cur_count=cur_count+$calctotalpieces 
                    WHERE equipment_id='$id' AND replaced=0";
                    $dbUpdatePart=dbexecutequery($sql);
                    $error.=$dbUpdatePart['error'];
                    
                    //update the PM tasks
                    $sql="UPDATE pm_instances SET cur_time=cur_time+$runningtime, cur_count=cur_count+$calctotalpieces 
                    WHERE equipment_id='$id' AND completed=0";
                    $dbUpdatePart=dbexecutequery($sql);
                    $error.=$dbUpdatePart['error'];
                    
                } 
            }
        }
    
        //now set wear applied to true
        $sql="UPDATE jobs_inserter_rundata SET wear_applied=1 WHERE id=$runData[id]";
        $dbUpdate=dbexecutequery($sql);
    }
    if($error!='')
    {
        setUserMessage('Problems applying wear and tear to the inserter for this package.<br>'.$error,'error');
    }    
}

function array_unshift_assoc(&$arr, $key, $val) 
{ 
    $arr = array_reverse($arr, true); 
    $arr[$key] = $val; 
    return array_reverse($arr, true); 
}


function send_ticket_message($owner,$ticket,$priorityname,$type,$maxed)
{
    $sql="SELECT * FROM users WHERE id=$ticket[submitted_by]";
    $dbSubmitted=dbselectsingle($sql);
    $submittedby=$dbSubmitted['data']['firstname'].' '.$dbSubmitted['data']['lastname'];
    
    if($_GET['mode']=='test')
    {
        print "For $type ticket#$ticket[id]: sending an email message to $owner that was submitted by $submittedby<br>";
    } else {
        $info.="For $type ticket#$ticket[id]: sending an email message to $owner that was submitted by $submittedby<br>";
    }
    
    $from=$GLOBALS['systemEmailFromAddress'];
    //$to="$fullname <$email>";
    $to=$owner;
    if($maxed)
    {
        $subject='Trouble ticket#'.$ticket['id'].' is at the most critical priority. Please address immediately.';
    } else {
        $subject='Trouble ticket#'.$ticket['id'].' has been escalated. Priority is now '.$priorityname.'.';
    }
    $message="<html><head></head><body>\n";
    $message.= "Hi, we just wanted to let you know that this ticket is still open and needs to be resolved as soon as possible.\n";
    
    if($type=='helpdesk')
    {
        $message.= "<a href='".$GLOBALS['serverIPaddress'].$GLOBALS['systemRootPath']."helpdeskTickets.php?action=edit&id=$ticket[id]'>Click here to view the ticket in the system</a>, or read it below.<br />\n\n";
        $message.="<p>Ticket submitted by $submittedby on ".date("m/d/Y",strtotime($ticket['submitted_datetime'])).' at '.date("h:i A",strtotime($ticket['submitted_datetime'])).$ticket['problem']."</p>\n";
        $message.="<p style='font-size:14px;font-weight:bold;'>Brief:</p>\n";
        $message.="<p>".$ticket['help_brief']."</p>\n";
        $message.="<p style='font-size:14px;font-weight:bold;'>Full Request:</p>\n";
        $message.="<p>".$ticket['help_request']."</p>\n";
        $message.="<br>";
    } else {
        //maintenance ticket
        $message.= "<a href='".$GLOBALS['serverIPaddress'].$GLOBALS['systemRootPath']."maintenanceTickets.php?action=edit&id=$ticket[id]'>Click here to view the ticket in the system</a>, or read it below.<br />\n\n";
        $message.="<p>Ticket submitted by $submittedby on ".date("m/d/Y",strtotime($ticket['submitted_datetime'])).' at '.date("h:i A",strtotime($ticket['submitted_datetime'])).$ticket['problem']."</p>\n";
        $message.="<p style='font-size:14px;font-weight:bold;'>Problem:</p>\n";
        $message.="<p>".$ticket['problem']."</p>\n";
        $message.="<p style='font-size:14px;font-weight:bold;'>Attempted Fix:</p>\n";
        $message.="<p>".$ticket['attempt']."</p>\n";
        $message.="<br>";
    }
    $message.="Mango Help Desk";
    $message.="</body></html>\n";
    $message = wordwrap($message, 70);

    $mail = new htmlMimeMail();
    
    $mail->setHtml($message);
    $mail->setFrom($from);
    $mail->setSubject($subject);
    $mail->send(array($to));
}

function loadHeadFiles($type='all',$appfield='mango')
{
    $scriptname=end(explode("/",$_SERVER['SCRIPT_NAME']));
    print "<!-- loading dynamic head files -->\n";
    if($type=='all' || $type='styles')
    {
        print "<!-- loading dynamic style files -->\n";
    //lets load the style sheets
        $sql="SELECT * FROM core_system_files WHERE file_type='style' AND head_load=1 AND $appfield=1 ORDER BY load_order ASC";
        $dbStyles=dbselectmulti($sql);
        if($dbStyles['numrows']>0)
        {
            foreach($dbStyles['data'] as $style)
            {
               $loadfor=explode(",",$style['specific_page']);
               if($style['specific_page']=='' || in_array($scriptname,$loadfor))
               {
                   $uptime=strtotime($style['file_moddate']); 
                   print "<link rel='stylesheet' type='text/css' href='styles/$style[file_name]?$uptime' />\n";     
               }       
            }
        }
    }
    if($type=='all' || $type=='scripts')
    {
        print "<!-- loading dynamic script files -->\n";
    
        //lets load the javascript files
        $sql="SELECT * FROM core_system_files WHERE file_type='script' AND head_load=1 AND $appfield=1 ORDER BY load_order ASC";
        $dbScripts=dbselectmulti($sql);
        if($dbScripts['numrows']>0)
        {
            foreach($dbScripts['data'] as $script)
            {
                $loadfor=explode(",",$script['specific_page']);
                $uptime=strtotime($script['file_moddate']); 
                if($script['specific_page']=='' || in_array($scriptname,$loadfor))
                {
                    print "<script type='text/javascript' src='includes/jscripts/$script[file_name]?$uptime'></script>\n";     
                } else {
                    print "<!-- when loading $script[file_name] looked for $script[specific_page] compared to $scriptname -->\n";
                }       
            }
        }
    }
    
    if($type=='all' || $type=='fixed')
    {
        print "<!-- loading fixed head files -->\n";
        $sql="SELECT google_map_key FROM core_preferences";
        $dbPrefs=dbselectsingle($sql);
        $key=stripslashes($dbPrefs['data']['google_map_key']);
        print "<script type='text/javascript' src='http://maps.google.com/maps/api/js?key=$key&sensor=false&libraries=drawing'></script>\n";
        
    }
}

function convertCSVtoArray($fileContent,$escape = '\\', $enclosure = '"', $delimiter = ',')
{
    $lines = array();
    $fields = array();

    if($escape == $enclosure)
    {
        $escape = '\\';
        $fileContent = str_replace(array('\\',$enclosure.$enclosure,"\r\n","\r"),
                    array('\\\\',$escape.$enclosure,"\\n","\\n"),$fileContent);
    }
    else
        $fileContent = str_replace(array("\r\n","\r"),array("\\n","\\n"),$fileContent);

    $nb = strlen($fileContent);
    $field = '';
    $inEnclosure = false;
    $previous = '';

    for($i = 0;$i<$nb; $i++)
    {
        $c = $fileContent[$i];
        if($c === $enclosure)
        {
            if($previous !== $escape)
                $inEnclosure ^= true;
            else
                $field .= $enclosure;
        }
        else if($c === $escape)
        {
            $next = $fileContent[$i+1];
            if($next != $enclosure && $next != $escape)
                $field .= $escape;
        }
        else if($c === $delimiter)
        {
            if($inEnclosure)
                $field .= $delimiter;
            else
            {
                //end of the field
                $fields[] = $field;
                $field = '';
            }
        }
        else if($c === "\n")
        {
            $fields[] = $field;
            $field = '';
            $lines[] = $fields;
            $fields = array();
        }
        else
            $field .= $c;
        $previous = $c;
    }
    //we add the last element
    if(true || $field !== '')
    {
        $fields[] = $field;
        $lines[] = $fields;
    }
    return $fields;
}

  
class pointLocation {
    var $pointOnVertex = true; // Check if the point sits exactly on one of the vertices

    function pointLocation() {
    }
    
    
        function pointInPolygon($point, $vertices, $pointOnVertex = true) {
        $this->pointOnVertex = $pointOnVertex;
        
        // Transform string coordinates into arrays with x and y values
        /*
        $vertices = array(); 
        foreach ($polygon as $vertex) {
            $vertices[] = $this->pointStringToCoordinates($vertex); 
        }
        */
        // Check if the point sits exactly on a vertex
        if ($this->pointOnVertex == true and $this->pointOnVertex($point, $vertices) == true) {
            return "vertex";
        }
        //print "Lat is $point[y] and Lon is $point[x] for the test point<br>";
        // Check if the point is inside the polygon or on the boundary
        $intersections = 0; 
        $vertices_count = count($vertices);
        //print "There are $vertices_count vertices in the polygon<br>";
        for ($i=1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i-1]; 
            $vertex2 = $vertices[$i];
            if ($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])) { // Check if point is on an horizontal polygon boundary
                return "boundary";
            }
            if ($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']) { 
                $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x']; 
                if ($xinters == $point['x']) { // Check if point is on the polygon boundary (other than horizontal)
                    return "boundary";
                }
                if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                    $intersections++; 
                }
            } 
        } 
        //print "Found a total of $intersections edges<br>";
        // If the number of edges we passed through is even, then it's in the polygon. 
        if ($intersections % 2 != 0) {
            return "inside";
        } else {
            return "outside";
        }
    }
    
    function pointOnVertex($point, $vertices) {
        foreach($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }
    
    }
     
} 

function var_log(&$varInput, $var_name='', $reference='', $method = '=', $sub = false) {

    static $output ;
    static $depth ;

    if ( $sub == false ) {
        $output = '' ;
        $depth = 0 ;
        $reference = $var_name ;
        $var = serialize( $varInput ) ;
        $var = unserialize( $var ) ;
    } else {
        ++$depth ;
        $var =& $varInput ;
        
    }
        
    // constants
    $nl = "\n" ;
    $block = 'a_big_recursion_protection_block';
    
    $c = $depth ;
    $indent = '' ;
    while( $c -- > 0 ) {
        $indent .= '|  ' ;
    }

    // if this has been parsed before
    if ( is_array($var) && isset($var[$block])) {
    
        $real =& $var[ $block ] ;
        $name =& $var[ 'name' ] ;
        $type = gettype( $real ) ;
        $output .= $indent.$var_name.' '.$method.'& '.($type=='array'?'Array':get_class($real)).' '.$name.$nl;
    
    // havent parsed this before
    } else {

        // insert recursion blocker
        $var = Array( $block => $var, 'name' => $reference );
        $theVar =& $var[ $block ] ;

        // print it out
        $type = gettype( $theVar ) ;
        switch( $type ) {
        
            case 'array' :
                $output .= $indent . $var_name . ' '.$method.' Array ('.$nl;
                $keys=array_keys($theVar);
                foreach($keys as $name) {
                    $value=&$theVar[$name];
                    var_log($value, $name, $reference.'["'.$name.'"]', '=', true);
                }
                $output .= $indent.')'.$nl;
                break ;
            
            case 'object' :
                $output .= $indent.$var_name.' = '.get_class($theVar).' {'.$nl;
                foreach($theVar as $name=>$value) {
                    var_log($value, $name, $reference.'->'.$name, '->', true);
                }
                $output .= $indent.'}'.$nl;
                break ;
            
            case 'string' :
                $output .= $indent . $var_name . ' '.$method.' "'.$theVar.'"'.$nl;
                break ;
                
            default :
                $output .= $indent . $var_name . ' '.$method.' ('.$type.') '.$theVar.$nl;
                break ;
                
        }
        
        // $var=$var[$block];
        
    }
    
    -- $depth ;
    
    if( $sub == false )
        return $output ;
        
}

  
function saveLayout($layoutid,$jobid)
{
    global $siteID;
    //now, lets create the plates for this job
    //we'll need to get the pub code for the publication
    //also, pub date and section codes for all sections
    $jobsql="SELECT A.*, B.pub_code FROM jobs A, publications B WHERE A.id=$jobid AND A.pub_id=B.id";
    //print "Job select sql:<br>$jobsql<br>";
    $dbJob=dbselectsingle($jobsql);
    $job=$dbJob['data'];

    //get some info about the run selected
    $sql="SELECT * FROM publications_runs WHERE id=$job[run_id]";
    $dbRun=dbselectsingle($sql);
    $runInfo=$dbRun['data'];
    $productcode=$runInfo['run_productcode'];
    
    $pubcode=$job['pub_code'];
    $pubdate=date("Y-md",strtotime($job['pub_date']));
    $pubid=$job['pub_id'];
    
    //we're actually build pages and plates at this time
    //existing pages/plates will be located first, in case it's just a color update
    $layouttime=date("Y-m-d H:i");
    $layoutby=$_SESSION['cmsuser']['userid'];
    $sql="UPDATE jobs SET layout_id=$layoutid, layoutset_time='$layouttime', layoutset_by='$layoutby' WHERE id=$jobid";
    $dbUpdate=dbexecutequery($sql);
    $error.=$dbUpdate['error'];
    

    $jobsection="SELECT * FROM jobs_sections WHERE job_id=$jobid";
    //print "Job section sql:<br>$jobsection<br>";
    $dbJSection=dbselectsingle($jobsection);
    $jsection=$dbJSection['data'];
    $scode[1]=$jsection['section1_code'];
    $scode[2]=$jsection['section2_code'];
    $scode[3]=$jsection['section3_code'];
    
    //now get layout sections
    $lsql="SELECT * FROM layout_sections WHERE layout_id=$layoutid";
    //print "Job layout sections sql:<br>$lsql<br>";
    $dbLSections=dbselectmulti($lsql);


    //first, delete any potential existing job plates and pages
    $sql="DELETE FROM job_pages WHERE job_id=$jobid";
    $dbDelete=dbexecutequery($sql);
    $sql="DELETE FROM job_plates WHERE job_id=$jobid";
    $dbDelete=dbexecutequery($sql);
    $colorconfigs=$GLOBALS['colorconfigs'];
    if ($dbLSections['numrows']>0)
    {
        foreach ($dbLSections['data'] as $lsection)
        {
            
            $section_number=$lsection['section_number'];
            $towers=$lsection['towers'];
            $towers=explode("|",$towers);
            foreach ($towers as $tower)
            {
                $created=date("Y-m-d H:i:s");
                //lets look up the color for a tower
                $sql="SELECT color_config FROM press_towers WHERE id=$tower";
                $dbColor=dbselectsingle($sql);
                if ($dbColor['numrows']>0)
                {
                    if ($dbColor['data']['color_config']=='K')
                    {
                        $color=0;
                        $spot=0;
                        $possiblecolor=0;
                    }else if ($dbColor['data']['color_config']=='K/S'){
                        $color=0;
                        $spot=1;
                        $possiblecolor=1;
                    }else{
                        $color=1;
                        $spot=0;
                        $possiblecolor=1;
                    }
                    $tcolor=array_search($dbColor['data']['color_config'],$colorconfigs,true);
                } else {
                    $color=0;
                    $possiblecolor=0;
                    $tcolor=0;
                }
                
                $plate1="";
                $plate2="";
                $pages1=array();
                $pages2=array();
                $lowpage1=9999; //set arbitrarily high so it gets set immediately to the new page
                $lowpage2=9999; //set arbitrarily high so it gets set immediately to the new page
                //now we need the pages for this layout & tower -- 10 side, then 13 side
                $psql="SELECT * FROM layout_page_config WHERE layout_id=$layoutid AND tower_id=$tower";
                $dbPages=dbselectmulti($psql);
                if ($dbPages['numrows']>0)
                {
                    foreach ($dbPages['data'] as $page)
                    {
                        $side=$page['side'];
                        $page_num=$page['page_number'];
                        if ($page_num!=0)
                        {
                            if ($side==10)
                            {
                                if ($page_num<$lowpage1 && $page_num!=0){$lowpage1=$page_num;}
                                $pages1[]="$pubid, $jobid,'$scode[$section_number]','$productcode', '$pubcode','$pubdate',$color,$spot,$possiblecolor,$tower, $tcolor,$page_num, 1,'$created', 1, '$siteID'),";
                            } else {
                                if ($page_num<$lowpage2 && $page_num!=0){$lowpage2=$page_num;}
                                $pages2[]="$pubid, $jobid,'$scode[$section_number]','$productcode', '$pubcode','$pubdate',$color,$spot,$possiblecolor,$tower, $tcolor, $page_num, 1,'$created', 1, '$siteID'),";
                            }
                        }            
                    
                    }
                    //now we should have 2 items, 2 arrays with pages and a low page number for each plate
                    $plate1="INSERT INTO job_plates (pub_id, job_id, section_code, run_productcode, pub_code, pub_date, low_page, color, spot, version, created, current, site_id) VALUES
                    ($pubid,$jobid, '$scode[$section_number]','$productcode', '$pubcode', '$pubdate','$lowpage1',$color,$spot, 1,'$created', 1, '$siteID')";
                    $dbPlate1=dbinsertquery($plate1);                                             
                    $error.=$dbPlate1['error'];
                    //print "Plate save 1 sql:<br>$plate1<br>";

                    $plate1ID=$dbPlate1['numrows'];
                    $plate2="INSERT INTO job_plates (pub_id, job_id, section_code, run_productcode, pub_code, pub_date, low_page, color, spot, version, created, current, site_id) VALUES
                    ($pubid,$jobid, '$scode[$section_number]','$productcode', '$pubcode', '$pubdate','$lowpage2',$color,$spot, 1,'$created', 1, '$siteID')";
                    $dbPlate2=dbinsertquery($plate2);
                    $plate2ID=$dbPlate2['numrows'];
                    $error.=$dbPlate2['error'];
                    //print "Plate save 2 sql:<br>$plate2<br>";

                    //now insert the pages
                    $values1="";
                    foreach($pages1 as $page)
                    {
                        $values1.="($plate1ID,$page";    
                    }
                    $values1=substr($values1,0,strlen($values1)-1);
                    $page1="INSERT INTO job_pages (plate_id, pub_id, job_id, section_code, run_productcode, pub_code, pub_date, color, spot, 
                    possiblecolor, tower_id, tower_color, page_number, version, created, current, site_id) VALUES $values1";
                    $dbPage1=dbinsertquery($page1);
                    $error.=$dbPage1['error'];
                    //print "Page save 1 sql:<br>$page1<br>";

                    //now insert the pages
                    $values2="";
                    foreach($pages2 as $page)
                    {
                        $values2.="($plate2ID,$page";    
                    }
                    $values2=substr($values2,0,strlen($values2)-1);
                    $page2="INSERT INTO job_pages (plate_id, pub_id, job_id, section_code, run_productcode, pub_code, pub_date, color, spot, 
                    possiblecolor, tower_id, tower_color, page_number, version, created, current, site_id) VALUES $values2";
                    $dbPage2=dbinsertquery($page2);
                    $error.=$dbPage2['error'];
                    //print "Page save 2 sql:<br>$page2<br>";

                }
            }
         }
    }
    if($error!='')
   {
       print $error;
   }
}

function checkCache($type,$parameters)
{
    $path=getcwd();
    if(strpos($path,"includes")>0)
    {
        if(strpos($path,"ajax_handlers")>0)
        {
            $path="../../cache/"; 
        } else {
            $path="../cache/";
        }  
    } else {
        $path="cache/";
    }
    if(substr($type,0,8)=='jobBoxes')
    {
        $jobid=str_replace('jobBoxes','',$type);
        $type='jobBoxes';
    }
    switch ($type)
    {
        case 'presscalendar':
            $filename=$type.'-'.$parameters.".txt";
        break;
        case 'jobBoxes':
            $filename=$type.'-'.$parameters.".txt";
        break;
        default:
        $filename='cache.txt';
        break;
    }
    $filename=$path.$filename;
    
    if(file_exists($filename))
    {
        return file_get_contents($filename);
    } else {
        return false;
    }    
}

function setCache($type,$parameters,$values)
{
    $path=getcwd();
    if(strpos($path,"includes")>0)
    {
        if(strpos($path,"ajax_handlers")>0)
        {
            $path="../../cache/"; 
        } else {
            $path="../cache/";
        }  
    } else {
        $path="cache/";
    }
    if(!file_exists($path))
    {
        mkdir($path);
    }
    if(substr($type,0,8)=='jobBoxes')
    {
        $jobid=str_replace('jobBoxes','',$type);
        $type='jobBoxes';
    }
    switch ($type)
    {
        case 'presscalendar':
            $filename=$type.'-'.$parameters.".txt";
        break;
        case 'jobBoxes':
            $filename=$type.'-'.$parameters.".txt";
        break;
        default:
            $filename='cache.txt';
        break;
    }
    //if cache directory does not exist, create it
    if(!file_exists($path))
    {
        mkdir($path,0777);
    }
    
    $filename=$path.$filename;
    $handle = fopen($filename, "w+");
    fwrite($handle, $values);
    fclose($handle);
    return $filename;
    
}

function clearCache($type)
{   
    //we'll just delete all cached files of that "type"
    $path=getcwd();
    if(strpos($path,"includes")>0)
    {
        if(strpos($path,"ajax_handlers")>0)
        {
            $path="../../cache/"; 
        } else {
            $path="../cache/";
        }  
    } else {
        $path="cache/";
    }
    $files = scandir($path);
    if(count($files)>0)
    {
        foreach($files as $id=>$file)
        {
            $t=explode("-",$file);
            $t=reset($t);
            if($t==$type)
            {
                unlink($path.$file);
            }    
        }
    }
}