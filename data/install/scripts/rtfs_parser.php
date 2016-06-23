<?php

/*
 * PHP script to import CIDOC-CRM definitions with easyrdf from a rdfs file to a postgresql database
 *
 * Author: alexander.watzinger@craws.net
 * Last update: 16.03.2016
 *
 * This script isn't needed for the installation of an OpenAtlas application.
 * It may be of use for a newer CIDOC CRM release.
 *
 * CIDOC CRM v5.1 was used with this script
 * Additionally the missing classes E59 -> E62 and shortcuts OA1 -> OA4 are added with this script
 *
 * Howto use:
 * - install easyrdf in the same folder https://github.com/njh/easyrdf
 * - you need an existing database structure like the one in 'structure.sql' *
 * - write a setting file 'setting.php' in same folder and with this content:
 *     <?php $db['db'] = 'databasename'; $db['host'] = 'host'; $db['user'] = 'user'; $db['password'] = 'password';
 *     or insert it here and comment the include statement for settings.php
 * - specify $rdfsFile: location of the rdfs file
 * - specify $dbTruncate: if true, all existing entries will be deleted
 *                        if false, it will be tried to add them but will fail on duplication errors
 *
  *
 */
$time = -time(true);
require 'vendor/autoload.php';
include_once 'settings.php';

/* configuration */
$rdfsFile = "http://localhost/net/parser/cidoc.rdfs";
$crmUrl = "http://www.cidoc-crm.org/cidoc-crm/";
$dbTruncate = true;   // if true, all existing entries will be deleted
$linebreak = "<br/>"; // linebreak for script output, use <br/> for html and \n in command line
$con = pg_connect(
    'host=' . $db['host'] .
    ' dbname=' . $db['db'] .
    ' user=' . $db['user'] .
    ' password=' . $db['password']
    ) or die("Connection to PostgreSQL failed\n");

/* cidoc standard classes that are missing in the rdfs file (see comment section of rdfs file) */
$missingClasses = [];
$missingClasses['E59'] = [
    'name' => 'Primitive Value',
    'label' => ['en' => 'Primitive Value', 'de' => 'Primitiver Wert'],
    'comment' => ['en' => 'This class comprises primitive values used as documentation elements, which are not further ' .
        'elaborated upon within the model. As such they are not considered as elements within our universe of discourse. ' .
        'No specific implementation recommendations are made. It is recommended that the primitive value system ' .
        'from the implementation platform be used to substitute for this class and its subclasses.']
];
$missingClasses['E60'] = [
    'name' => 'Number',
    'subClassOf' => ['E59'],
    'label' => ['en' => 'Number', 'de' => 'Zahl'],
    'comment' => ['en' => 'This class comprises any encoding of computable (algebraic) values such as integers, real numbers, ' .
        'complex numbers, vectors, tensors etc., including intervals of these values to express limited precision. ' .
        'Numbers are fundamentally distinct from identifiers in continua, such as instances of E50 Date ' .
        'and E47 Spatial Coordinate, even though their encoding may be similar. Instances of E60 ' .
        'Number can be combined with each other in algebraic operations to yield other instances of ' .
        'E60 Number, e.g., 1+1=2. Identifiers in continua may be combined with numbers expressing ' .
        'distances to yield new identifiers, e.g., 1924-01-31 + 2 days = 1924-02-02. Cf. E54 Dimension']
];
$missingClasses['E61'] = [
    'name' => 'Time Primitive',
    'subClassOf' => ['E59'],
    'label' => ['en' => 'Time Primitive', 'de' => 'Zeitprimitiv'],
    'comment' => ['en' => 'This class comprises instances of E59 Primitive Value for time that should be implemented ' .
        'with appropriate validation, precision and interval logic to express date ranges relevant to ' .
        'cultural documentation. E61 Time Primitive is not further elaborated upon within the model.']
];
$missingClasses['E62'] = [
    'name' => 'String',
    'subClassOf' => ['E59'],
    'label' => ['en' => 'String', 'de' => 'Zeichenkette'],
    'comment' => ['en' => 'This class comprises the instances of E59 Primitive Values used for documentation such as ' .
        'free text strings, bitmaps, vector graphics, etc. E62 String is not further elaborated upon within the model']
];

$shortcuts = [];
$shortcuts['OA1'] = [
    'name' => 'begins chronologically',
    'domain' => 'E77',
    'range' => 'E61',
    'label' => ['en' => 'begins chronologically', 'de' => 'beginnt chronologisch'],
    'comment' => ['en' => "OA1 is used to link the beginning of a persistent item's (E77) life span (or time of usage)
    with a certain date in time. E77 (Persistent Item) - P92i (was brought into existence by) - E63 (Beginning of Existence)
    - P4 (has time span) - E52 (Time Span) - P81 (ongoing throughout) - E61 (Time Primitive)
    Example: [Holy Lance (E22)] was brought into existence by [forging of Holy Lance (E12)] has time span
    [Moment/Duration of Forging of Holy Lance (E52)] ongoing througout [0770-12-24 (E61)]"]
];
$shortcuts['OA2'] = [
    'name' => 'ends chronologically',
    'domain' => 'E77',
    'range' => 'E61',
    'label' => ['en' => 'ends chronologically', 'de' => 'endet chronologisch'],
    'comment' => ['en' => "OA2 is used to link the end of a persistent item's (E77) life span (or time of usage) with
    a certain date in time.
    E77 (Persistent Item) - P93i (was taken out of existence by) - E64 (End of Existence) - P4 (has time span) -
    E52 (Time Span) - P81 (ongoing throughout) - E61 (Time Primitive)
    Example: [The one ring (E22)] was destroyed by [Destruction of the one ring (E12)] has time span
    [Moment of throwing it down the lava (E52)] ongoing througout [3019-03-25 (E61)]"]
];
$shortcuts['OA3'] = [
    'name' => 'born chronologically',
    'domain' => 'E21',
    'range' => 'E61',
    'label' => ['en' => 'born chronologically', 'de' => 'geboren chronologisch'],
    'comment' => ['en' => "OA3 is used to link the birth of a person with a certain date in time.
    21 (Person) - P98i (was born) by - E67 (Birth) - P4 (has time span) - E52 (Time Span) - P81 (ongoing throughout) -
    E61 (Time Primitive)
    Example: [Stefan (E21)] was born by [birth of Stefan (E12)] has time span
    [Moment/Duration of Stefan's birth (E52)] ongoing througout [1981-11-23 (E61)]"]
];
$shortcuts['OA4'] = [
    'name' => 'died chronologically',
    'domain' => 'E21',
    'range' => 'E61',
    'label' => ['en' => 'died chronologically', 'de' => 'gestorben chronologisch'],
    'comment' => ['en' => "OA4 is used to link the death of a person with a certain date in time.
    E21 (Person) - P100i (died in) - E69 (Death) - P4 (has time span) - E52 (Time Span) - P81 (ongoing throughout) -
    E61 (Time Primitive)
    Example: [Lady Diana (E21)] died in [death of Diana (E69)] has time span [Moment/Duration of Diana's death (E52)]
    ongoing througout [1997-08-31 (E61)]"]
];
$shortcuts['OA5'] = [
    'name' => 'begins chronologically',
    'domain' => 'E2',
    'range' => 'E61',
    'label' => ['en' => 'begins chronologically', 'de' => 'beginnt chronologisch'],
    'comment' => ['en' => "OA5 is used to link the beginning of a temporal entity (E2) with a certain date in time.
    It can also be used to determine the beginning of a property's duration.
    E2 (Temporal Entity) - P4 (has time span) - E52 (Time Span) - P81 (ongoing throughout) - E61 (Time Primitive)
    Example: [ Thirty Years' War (E7)] has time span [Moment/Duration of Beginning of Thirty Years' War (E52)] ongoing
    througout [1618-05-23 (E61)]"]
];
$shortcuts['OA6'] = [
    'name' => 'ends chronologically',
    'domain' => 'E2',
    'range' => 'E61',
    'label' => ['en' => 'ends chronologically', 'de' => 'endet chronologisch'],
    'comment' => ['en' => "OA6 is used to link the end of a temporal entity's (E2) with a certain date in time.
    It can also be used to determine the end of a property's duration.
    E2 (temporal entity) - P4 (has time span) - E52 (Time Span) - P81 (ongoing throughout) - E61 (Time Primitive)
    Example: [ Thirty Years' War (E7)] has time span [Moment/Duration of End of Thirty Years' War (E52)] ongoing
    througout [1648-10-24 (E61)]"]
];
$shortcuts['OA7'] = [
    'name' => 'has relationship to',
    'domain' => 'E39',
    'range' => 'E39',
    'label' => ['en' => 'has relationship to', 'de' => 'hat Beziehung zu'],
    'comment' => ['en' => "OA7 is used to link two Actors (E39) via a certain relationship E39 Actor linked with
    E39 Actor: E39 (Actor) - P11i (participated in) - E5 (Event) - P11 (had participant) - E39 (Actor) Example:
    [ Stefan (E21)] participated in [ Relationship from Stefan to Joachim (E5)] had participant [Joachim (E21)] The
    connecting event is defined by an entity of class E55 (Type): [Relationship from Stefan to Joachim (E5)] has type
    [Son to Father (E55)]"]
];
$shortcuts['OA8'] = [
    'name' => 'appears for the first time in',
    'domain' => 'E77',
    'range' => 'E53',
    'label' => ['en' => 'appears for the first time in', 'de' => 'erscheint erstes mal in'],
    'comment' => ['en' => "OA9 is used to link the beginning of a persistent item's (E77) life span (or time of
    usage) with a certain place. E.g to document the birthplace of a person. E77 Persistent Item linked with a E53
    Place: E77 (Persistent Item) - P92i (was brought into existence by) - E63 (Beginning of Existence) - P7 (took
    place at) - E53 (Place) Example: [Albert Einstein (E21)] was brought into existence by [Birth of Albert Einstein
    (E12)] took place at [Ulm (E53)]"]
];
$shortcuts['OA9'] = [
    'name' => 'appears for the last time in',
    'domain' => 'E77',
    'range' => 'E53',
    'label' => ['en' => 'appears for the last time in', 'erscheint zuletzt in'],
    'comment' => ['en' => "OA10 is used to link the end of a persistent item's (E77) life span (or time of usage)
    with a certain place. E.g to document a person's place of death. E77 Persistent Item linked with a E53 Place:
    E77 (Persistent Item) - P93i (was taken out of existence by) - E64 (End of Existence) - P7 (took place at) - E53
    (Place) Example: [Albert Einstein (E21)] was taken out of by [Death of Albert Einstein (E12)] took place at
    [Princeton (E53)]"]
];

echo "Script begin" . $linebreak . $linebreak;

/* import data from rdfs file */
$graph = EasyRdf_Graph::newAndLoad($rdfsFile, 'rdfxml');
$entries = $graph->toRdfPhp();
$classData = [];
$propertyData = [];
foreach ($entries as $key => $value) {
    $name = str_replace($crmUrl, '', $key);
    $firstCharacter = $name[0];
    switch ($firstCharacter) {
        case "E":
            $classData[$name] = $value;
            break;
        case "P":
            $propertyData[$name] = $value;
            break;
        default:
            echo "unkown cidoc type: " . $firstCharacter;
            break;
    }
}

/* prepare arrays for database import  */
$classes = [];
foreach ($classData as $identifier => $data) {
    list($code, $name) = explode('_', $identifier, 2);
    $classes[$code] = ['name' => str_replace("_", " ", $name)];
    foreach ($data as $key => $value) {
        if (strpos($key, "rdf-schema#subClassOf") !== false) {
            foreach ($value as $superClass) {
                list($superCode) = explode('_', $superClass['value'], 2);
                $classes[$code]['subClassOf'][] = str_replace($crmUrl, "", $superCode);
            }
        }
        if (strpos($key, "rdf-schema#label") !== false) {
            foreach ($value as $language) {
                $classes[$code]['label'][$language['lang']] = $language['value'];
            }
        }
        if (strpos($key, "rdf-schema#comment") !== false) {
            $classes[$code]['comment']['en'] = $value[0]['value'];
        }
    }
}
$allClasses = array_merge($classes, $missingClasses);

$properties = [];
foreach ($propertyData as $identifier => $data) {
    list($code, $name) = explode('_', $identifier, 2);
    if (substr($code, -1) != 'i') {
        $properties[$code] = ['name' => str_replace("_", " ", $name)];
        foreach ($data as $key => $value) {
            if (strpos($key, "rdf-schema#label") !== false) {
                foreach ($value as $language) {
                    $properties[$code]['label'][$language['lang']] = $language['value'];
                }
            }
            if (strpos($key, "rdf-schema#subPropertyOf") !== false) {
                foreach ($value as $superProperty) {
                    list($superCode) = explode('_', $superProperty['value'], 2);
                    $properties[$code]['subPropertyOf'][] = str_replace($crmUrl, "", $superCode);
                }
            }
            if (strpos($key, "rdf-schema#comment") !== false) {
                $properties[$code]['comment']['en'] = $value[0]['value'];
            }
            if (strpos($key, "rdf-schema#domain") !== false) {
                $domain = str_replace($crmUrl, '', $value[0]['value']);
                list($domainCode) = explode('_', $domain);
                $properties[$code]['domain'] = $domainCode;
            }
            if (strpos($key, "rdf-schema#range") !== false) {
                /* replace literals with classes from "Concept Reference Model" pdf Version 5.0.4 */
                if ($value[0]['value'] == 'http://www.w3.org/2000/01/rdf-schema#Literal') {
                    switch ($code) {
                        case 'P3':
                            $properties[$code]['range'] = 'E63';
                            break;
                        case 'P57':
                            $properties[$code]['range'] = 'E26';
                            break;
                        case 'P79':
                        case 'P80':
                            $properties[$code]['range'] = 'E62';
                            break;
                        case 'P81':
                        case 'P82':
                            $properties[$code]['range'] = 'E61';
                            break;
                        case 'P90':
                            $properties[$code]['range'] = 'E60';
                            break;
                        default:
                            echo "unexpected literal range for code: " + $code + $linebreak;
                            exit;
                    }
                } else {
                    $range = str_replace($crmUrl, '', $value[0]['value']);
                    list($rangeCode) = explode('_', $range);
                    $properties[$code]['range'] = $rangeCode;
                }
            }
        }
    } else { // inverse direction
        $code = substr($code, 0, -1);
        $properties[$code]['nameInverse'] = str_replace("_", " ", $name);
        foreach ($data as $key => $value) {
            if (strpos($key, "rdf-schema#label") !== false) {
                foreach ($value as $language) {
                    $properties[$code]['labelInverse'][$language['lang']] = $language['value'];
                }
            }
        }
    }
}
$allProperties = array_merge($properties, $shortcuts);

/* clean up */
if ($dbTruncate) {
    echo "truncating tables" . $linebreak . $linebreak;
    pg_query($con, 'TRUNCATE
    model.class,
    model.class_inheritance,
    model.i18n,
    model.property,
    model.property_inheritance
    RESTART IDENTITY CASCADE;') or dbError("Truncation", __LINE__);
}

/* import to database */
pg_query($con, "BEGIN;");

$countClass = 0;
$countClassInheritance = 0;
$countProperty = 0;
$countPropertyInheritance = 0;
$countComments = 0;
$countNameTranslations = 0;

$sqlClass = 'INSERT INTO model.class (code, name) VALUES ($1, $2) RETURNING id;';
foreach ($allClasses as $code => $class) {
    $query = pg_query_params($con, $sqlClass, [$code, $class['name']]) or dbError("class", __LINE__);
    $row = pg_fetch_row($query);
    $allClasses[$code]['id'] = $row['0'];
    $countClass++;
}

$sqlClassName = "INSERT INTO model.i18n (table_name, table_field, table_id, language_code, text)
  VALUES ('class', 'name', $1, $2, $3);";
$sqlClassComment = "INSERT INTO model.i18n (table_name, table_field, table_id, language_code, text)
  VALUES ('class', 'comment', $1, $2, $3);";
$sqlClassSuper = 'INSERT INTO model.class_inheritance (super_id, sub_id) VALUES (
  (SELECT id FROM model.class WHERE code LIKE $1),$2);';
foreach ($allClasses as $code => $class) {
    foreach ($class['subClassOf'] as $superClass) {
        pg_query_params($con, $sqlClassSuper, [$superClass, $class['id']]) or dbError("subClass", __LINE__);
        $countClassInheritance++;
    }
    foreach ($class['label'] as $languageCode => $text) {
        pg_query_params($con, $sqlClassName, [$class['id'], $languageCode, $text]) or dbError("class name", __LINE__);
        $countNameTranslations++;
    }
    foreach ($class['comment'] as $languageCode => $text) {
        pg_query_params($con, $sqlClassComment, [$class['id'], $languageCode, $text]) or dbError("class comment", __LINE__);
        $countComments++;
    }
}

$sqlProperty = 'INSERT INTO model.property (code, name, name_inverse, domain_class_id, range_class_id) VALUES (
  $1,
  $2,
  $3,
  (SELECT id FROM model.class WHERE code LIKE $4),
  (SELECT id FROM model.class WHERE code LIKE $5)) RETURNING id;';
foreach ($allProperties as $code => $property) {
    $query = pg_query_params($con, $sqlProperty, [
        $code,
        $property['name'],
        $property['nameInverse'],
        $property['domain'],
        $property['range']]) or dbError("property" . $property['range'], __LINE__);
    $row = pg_fetch_row($query);
    $allProperties[$code]['id'] = $row['0'];
    $countProperty++;
}

$sqlPropertyName = "INSERT INTO model.i18n (table_name, table_field, table_id, language_code, text)
  VALUES ('property', 'name', $1, $2, $3);";
$sqlPropertyNameInverse = "INSERT INTO model.i18n (table_name, table_field, table_id, language_code, text)
  VALUES ('property', 'name_inverse', $1, $2, $3);";
$sqlPropertyComment = "INSERT INTO model.i18n (table_name, table_field, table_id, language_code, text)
  VALUES ('property', 'comment', $1, $2, $3);";
$sqlPropertySuper = 'INSERT INTO model.property_inheritance (super_id, sub_id) VALUES (
  (SELECT id FROM model.property WHERE code LIKE $1),$2);';

foreach ($allProperties as $code => $property) {
    foreach ($property['label'] as $languageCode => $text) {
        pg_query_params($con, $sqlPropertyName, [$property['id'], $languageCode, $text]) or dbError("property name", __LINE__);
        $countNameTranslations++;
    }
    foreach ($property['subPropertyOf'] as $superProperty) {
        pg_query_params($con, $sqlPropertySuper, [$superProperty, $property['id']]) or dbError("subProperty", __LINE__);
        $countPropertyInheritance++;
    }
    foreach ($property['labelInverse'] as $languageCode => $text) {
        pg_query_params($con, $sqlPropertyNameInverse, [$property['id'], $languageCode, $text]) or dbError("property inverse name", __LINE__);
        $countNameTranslations++;
    }
    foreach ($property['comment'] as $languageCode => $text) {
        pg_query_params($con, $sqlPropertyComment, [$property['id'], $languageCode, $text]) or dbError("property comment", __LINE__);
        $countComments++;
    }
}

echo "imported " . $countClass . " classes" . $linebreak;
echo "imported " . $countClassInheritance . " class inheritance relations" . $linebreak;
echo "imported " . $countProperty . " properties" . $linebreak;
echo "imported " . $countPropertyInheritance . " property inheritance relations" . $linebreak;
echo "imported " . $countComments . " comments" . $linebreak;
echo "imported " . $countNameTranslations . " translations" . $linebreak;

pg_query($con, "COMMIT;");
pg_close($con);
$time += time(true);
echo $linebreak . "Script end - execution time: " . $time . " seconds" . $linebreak;

function dbError($message, $line) {
    global $linebreak;
    echo $message . " failed. " . pg_last_error() . $linebreak . 'Error in line ' . $line . $linebreak;
    exit;
}
