<?php
/**
 * Minimal WordPress function/class stubs for unit testing.
 * Only the functions called by the plugin classes under test are stubbed here.
 */

// ---------- Options ----------
$_wp_options = array();

function get_option(string $key, $default = false) {
    global $_wp_options;
    return array_key_exists($key, $_wp_options) ? $_wp_options[$key] : $default;
}

function update_option(string $key, $value): bool {
    global $_wp_options;
    $_wp_options[$key] = $value;
    return true;
}

function add_option(string $key, $value): bool {
    global $_wp_options;
    if (!array_key_exists($key, $_wp_options)) {
        $_wp_options[$key] = $value;
        return true;
    }
    return false;
}

function delete_option(string $key): bool {
    global $_wp_options;
    unset($_wp_options[$key]);
    return true;
}

// ---------- Sanitization / escaping ----------
function sanitize_text_field(string $str): string {
    return trim(strip_tags($str));
}

function sanitize_textarea_field(string $str): string {
    return trim($str);
}

function esc_attr(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function esc_html(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function esc_url_raw(string $url): string {
    return filter_var($url, FILTER_SANITIZE_URL);
}

function wp_strip_all_tags(string $str): string {
    return strip_tags($str);
}

function wp_kses_post(string $str): string {
    return $str;
}

function wp_unslash($value) {
    return is_array($value) ? array_map('wp_unslash', $value) : stripslashes((string) $value);
}

// ---------- Salts ----------
function wp_salt(string $scheme = 'auth'): string {
    return 'test-salt-for-unit-tests-only-not-secure';
}

// ---------- Transients ----------
$_wp_transients = array();

function get_transient(string $key) {
    global $_wp_transients;
    return isset($_wp_transients[$key]) ? $_wp_transients[$key] : false;
}

function set_transient(string $key, $value, int $expiration = 0): bool {
    global $_wp_transients;
    $_wp_transients[$key] = $value;
    return true;
}

function delete_transient(string $key): bool {
    global $_wp_transients;
    unset($_wp_transients[$key]);
    return true;
}

// ---------- Encryption ----------
if (!function_exists('openssl_encrypt')) {
    function openssl_encrypt(string $data, string $method, string $key, int $options = 0, string $iv = ''): string {
        return base64_encode($data);
    }
    function openssl_decrypt(string $data, string $method, string $key, int $options = 0, string $iv = '') {
        return base64_decode($data);
    }
    function openssl_random_pseudo_bytes(int $length): string {
        return str_repeat("\x00", $length);
    }
}

// ---------- i18n ----------
function __(string $text, string $domain = 'default'): string {
    return $text;
}

function _e(string $text, string $domain = 'default'): void {
    echo $text;
}

// ---------- HTTP ----------
class WP_Error {
    private string $code;
    private string $message;

    public function __construct(string $code = '', string $message = '') {
        $this->code    = $code;
        $this->message = $message;
    }

    public function get_error_message(): string {
        return $this->message;
    }
}

function is_wp_error($thing): bool {
    return $thing instanceof WP_Error;
}

function wp_remote_get(string $url, array $args = array()) {
    return new WP_Error('not_implemented', 'HTTP not available in tests');
}

function wp_remote_post(string $url, array $args = array()) {
    return new WP_Error('not_implemented', 'HTTP not available in tests');
}

function wp_remote_retrieve_body($response): string {
    return '';
}

// ---------- Posts ----------
$_wp_posts = array();
$_wp_post_meta = array();

function wp_insert_post(array $data, bool $wp_error = false) {
    global $_wp_posts;
    $id = count($_wp_posts) + 1;
    $_wp_posts[$id] = $data + array('ID' => $id, 'post_status' => 'draft');
    return $id;
}

function wp_update_post(array $data, bool $wp_error = false): int {
    global $_wp_posts;
    if (isset($data['ID'])) {
        $_wp_posts[$data['ID']] = array_merge($_wp_posts[$data['ID']] ?? array(), $data);
    }
    return (int) ($data['ID'] ?? 0);
}

function wp_delete_post(int $post_id, bool $force = false) {
    global $_wp_posts;
    if (isset($_wp_posts[$post_id])) {
        unset($_wp_posts[$post_id]);
        return (object) array('ID' => $post_id);
    }
    return false;
}

function get_post_meta(int $post_id, string $key = '', bool $single = false) {
    global $_wp_post_meta;
    if ($single) {
        return $_wp_post_meta[$post_id][$key][0] ?? '';
    }
    return $_wp_post_meta[$post_id][$key] ?? array();
}

function update_post_meta(int $post_id, string $key, $value): bool {
    global $_wp_post_meta;
    $_wp_post_meta[$post_id][$key] = array($value);
    return true;
}

function get_posts(array $args = array()): array {
    return array();
}

function wp_set_post_categories(int $post_id, array $categories): array {
    return $categories;
}

function wp_set_post_tags(int $post_id, $tags = array()): array {
    return (array) $tags;
}

function get_edit_post_link(int $post_id, string $context = 'display'): string {
    return 'https://example.com/wp-admin/post.php?post=' . $post_id . '&action=edit';
}

function wpautop(string $pee, bool $br = true): string {
    return $pee;
}

function wp_strip_all_tags_deep($value) {
    return is_array($value) ? array_map('wp_strip_all_tags_deep', $value) : strip_tags((string) $value);
}

// ---------- Time ----------
function current_time(string $type, bool $gmt = false): string {
    if ($type === 'mysql') {
        return date('Y-m-d H:i:s');
    }
    if ($type === 'timestamp') {
        return (string) time();
    }
    return date($type);
}

// ---------- Database ----------
global $wpdb;
$wpdb = new class {
    public string $prefix = 'wp_';

    /** @var array<string,array<int,array<string,mixed>>> */
    private array $tables = array();

    public function get_charset_collate(): string {
        return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    }

    public function prepare(string $query, ...$args): string {
        $query = str_replace('%s', "'%s'", $query);
        $query = str_replace('%d', '%d', $query);
        return vsprintf($query, $args);
    }

    public function get_var(string $query): ?string {
        // Return 0 for COUNT queries so deduplication always returns false in tests
        if (stripos($query, 'COUNT(') !== false) {
            return '0';
        }
        return null;
    }

    public function get_results(string $query): array {
        return array();
    }

    public function insert(string $table, array $data, array $format = array()): int {
        $this->tables[$table][] = $data;
        return 1;
    }

    public function delete(string $table, array $where, array $format = array()): int {
        return 1;
    }
};

// ---------- Settings errors ----------
$_wp_settings_errors = array();

function add_settings_error(string $setting, string $code, string $message, string $type = 'error'): void {
    global $_wp_settings_errors;
    $_wp_settings_errors[] = compact('setting', 'code', 'message', 'type');
}

function get_settings_errors(string $setting = '', bool $sanitize = false): array {
    global $_wp_settings_errors;
    return $_wp_settings_errors;
}

// ---------- Misc ----------
function get_current_user_id(): int {
    return 1;
}

function wp_list_pluck(array $list, string $field): array {
    return array_column($list, $field);
}

function get_categories(array $args = array()): array {
    return array();
}

function human_time_diff(int $from, int $to = 0): string {
    return '1 minute';
}

function apply_filters(string $hook, $value, ...$args) {
    return $value;
}

function add_action(string $hook, $callback, int $priority = 10, int $accepted_args = 1): void {}
function add_filter(string $hook, $callback, int $priority = 10, int $accepted_args = 1): void {}
function do_action(string $hook, ...$args): void {}

function get_bloginfo(string $show = '', string $filter = 'raw'): string {
    return $show === 'version' ? '6.5' : '';
}

function plugin_basename(string $file): string {
    return basename(dirname($file)) . '/' . basename($file);
}

function deactivate_plugins($plugins): void {}

function wp_die(string $message = '', $title = '', array $args = array()): void {
    throw new RuntimeException($message);
}

function current_user_can(string $capability): bool {
    return true;
}

function admin_url(string $path = ''): string {
    return 'https://example.com/wp-admin/' . ltrim($path, '/');
}

function wp_create_nonce($action = ''): string {
    return 'test_nonce';
}

function wp_verify_nonce(?string $nonce, $action = ''): int {
    return 1;
}

function wp_send_json_success($data = null): void {
    echo json_encode(array('success' => true, 'data' => $data));
}

function wp_send_json_error($data = null): void {
    echo json_encode(array('success' => false, 'data' => $data));
}

function wp_clear_scheduled_hook(string $hook): void {}
function wp_next_scheduled(string $hook): bool { return false; }
function wp_schedule_event(int $timestamp, string $recurrence, string $hook): void {}

function register_activation_hook($file, $callback): void {}
function register_deactivation_hook($file, $callback): void {}

function add_options_page($page_title, $menu_title, $capability, $menu_slug, $callback = ''): void {}
function register_setting($option_group, $option_name, $args = array()): void {}
function add_settings_section($id, $title, $callback, $page): void {}
function add_settings_field($id, $title, $callback, $page, $section = 'default', $args = array()): void {}

function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false): void {}
function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all'): void {}
function wp_localize_script($handle, $object_name, $l10n): bool { return true; }

function settings_fields($option_group): void {}
function do_settings_sections($page): void {}
function settings_errors($setting = '', $sanitize = false, $hide_on_update = false): void {}
function submit_button($text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null): void {}
function get_admin_page_title(): string { return 'AI Auto News Poster'; }
function selected($selected, $current, bool $echo = true): string { return $selected == $current ? ' selected="selected"' : ''; }

