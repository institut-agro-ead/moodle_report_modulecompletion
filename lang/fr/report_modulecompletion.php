<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * FR lang strings.
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = "Suivi d'achèvement des activités";
$string['meta_settings'] = 'Paramètres des métadonnées';
$string['modulecompletion:view'] = "Accéder aux rapports d'achèvement d'activités";
$string['categoryname'] = "Rapports d'activités";
$string['configmodulecompletion'] = 'Avancement des activités';
$string['modules_list_label'] = 'Liste des activités';
$string['modules_list_description'] = 'Choisissez les activités à prendre en compte dans le suivi des activités des apprenants';
$string['use_metadata_label'] = 'Utiliser le plugin metadata';
$string['use_metadata_description'] = 'Si le plugin metadata est installé sur votre Moodle, vous pouvez afficher les métadonnées associées aux activités';
$string['metadata_list_label'] = 'Choisissez les métadonnées';
$string['metadata_list_description'] = 'Sélectionnez les métadonnées à afficher dans les rapports';
$string['numeric_metadata_list_label'] = 'Choisissez les métadonnées numeriques';
$string['numeric_metadata_list_description'] = 'Sélectionnez les métadonnées pouvant être considérées comme numériques. Un total sera calculé pour chaque cours, et un total pour chaque apprenant';
$string['numeric_metadata_conversion'] = 'Conversion des métadonnées';
$string['numeric_metadata_conversion_description'] = '<p>Choisissez comment convertir les métadonnées numériques. Entrez une formule de calcul à appliquer à la valeur.<br> <strong>Example :</strong> pour transformer une métadonnée <strong>minutes</strong> en heures, il suffit de diviser la valeur par 60. Entrez donc <strong>/60</strong><br><em>NB : Les opérateurs acceptés sont : <strong>+</strong>, <strong>-</strong>, <strong>*</strong>, <strong>/</strong>, <strong>%</strong>. L\'utilisation de parenthèses est expérimentale et pourraît ne pas fonctionner correctement</em>.</p><p>Choisissez ensuite un libellé pour la valeur convertie, exemple : <strong>heure(s)</strong></p>';
$string['numeric_metadata_formula'] = '(Formule)';
$string['numeric_metadata_formula_description'] = 'Si la formule est incorrecte, elle sera ignorée.';
$string['numeric_metadata_label'] = '(Libellé)';

// Sql formats.
$string['month_date_format'] = 'm-Y';
$string['full_date_format'] = 'd-m-Y';

// Form.
$string['user_label'] = 'Entrez un apprenant';
$string['user_placeholder'] = 'Nom';
$string['cohort_label'] = 'Entrez une cohorte';
$string['cohort_placeholder'] = 'Nom';
$string['cohorts'] = 'Cohortes';
$string['course_label'] = 'Entrez un cours';
$string['course_placeholder'] = 'Nom';

$string['form_filter_name'] = 'Nom du filtre';
$string['form_filter_name_placeholder'] = 'Nom';
$string['form_save_filter'] = 'Enregistrer le filtre';
$string['form_only_cohorts_courses'] = 'Seulement les cours des cohortes';
$string['form_only_cohorts_courses_help'] = 'Afficher seulement les cours et activités associés aux cohortes sélectionnées';
$string['form_starting_date'] = 'Date de début';
$string['form_ending_date'] = 'Date de fin';
$string['form_order_by_column'] = 'Trié par';
$string['form_order_by_type'] = 'Par ordre';
$string['form_order_by_student'] = 'Apprenant';
$string['form_order_by_completion'] = "Pourcentage d'achèvement";
$string['form_order_by_last_completed'] = 'Date de dernier achèvement';
$string['form_order_by_asc'] = 'Croissant';
$string['form_order_by_desc'] = 'Décroissant';
$string['form_quickfilter_submit'] = 'Filtrer';
$string['form_quickfilter_name'] = 'Filtre rapide';

// Form errors.
$string['form_name_required'] = 'Vous devez donner un nom à votre filtre';
$string['form_missing_starting_date'] = 'La date de début doit être renseignée et correctement formattée';
$string['form_missing_ending_date'] = 'La date de fin doit être renseignée et correctement formattée';
$string['form_starting_date_must_be_anterior'] = 'La date de début doit être antérieure à la date de fin';
$string['form_user_not_found'] = "L'utilisateur demandé n'existe pas";
$string['form_cohort_not_found'] = "La cohorte demandée n'existe pas";
$string['form_course_not_found'] = "Le cours demandé n'existe pas";

// Templates.
$string['max_achievement_percentage'] = 'Pourcentage maximum obtenu par un apprenant';
$string['reports_count'] = 'Nombre de résultats';
$string['completed_modules'] = 'activités complétées';
$string['last_completion_date'] = "Date de dernier achèvement d'activité";
$string['has_restrictions'] = 'Ce cours contient une ou plusieurs sections/activités comportant une ou plusieurs restrictions. Ces activités seront comptabilisées dans le nombre total d\'activités du cours même si l\'apprenant n\'y a pas accès.';
$string['backtofilters'] = 'Retour aux filtres';
$string['no_reports'] = 'Aucun résultat';
$string['expand'] = 'Déplier';
$string['collapse'] = 'Replier';
$string['show_all'] = 'Tout Afficher';
$string['hide_all'] = 'Tout Cacher';

$string['your_filters'] = 'Vos filtres';
$string['quick_filter'] = 'Filtre instantané';
$string['add_filter'] = 'Ajouter un nouveau filtre';
$string['load_filter_title'] = 'Charger ce filtre';
$string['edit_filter_title'] = 'Éditer ce filtre';
$string['copy_filter_title'] = 'Dupliquer ce filtre';
$string['delete_filter_title'] = 'Supprimer ce filtre';

// Modal.
$string['confirm_filter_deletion'] = 'Êtes-vous sûr de vouloir supprimer ce filtre ?';

// Error.
$string['no_template'] = 'Ce plugin utilise des templates définis dans le thème Boost. Votre thème devrait hériter de Boost.';
$string['filter_id_required'] = "L'id du filtre doit être renseigné";
$string['filter_not_found'] = "Ce n'est pas le filtre que vous recherchez...";
$string['export_type_required'] = "Le type d'export doit être renseigné (csv ou xlsx)";

// Table headers.
$string['month_header'] = 'Mois';
$string['user_header'] = 'Nom apprenant';
$string['user_email_header'] = 'Email apprenant';
$string['course_header'] = 'Nom du cours';
$string['section_header'] = 'Nom de la section';
$string['module_type_header'] = "Type de l'activité";
$string['module_header'] = "Nom de l'activité";
$string['completed_header'] = 'Complétée le';
$string['course_completed_header'] = 'Activités terminées pour le cours';
$string['course_completed_percent_header'] = 'Activités en pourcentage';
$string['total_completed_header'] = 'Total des activités terminées';
$string['total_completed_percent_header'] = 'Total en pourcentage';

// Privacy.
$string['privacy:metadata'] = 'Le plugin ModuleCompletion n\'enregistre pas de données personnelles.';
