<?php defined('ABSPATH') || exit; ?>
<style>
    .lektrail-settings .postbox .hndle {
        padding: 10px 12px;
        margin: 0;
    }
    .lektrail-settings .postbox .inside {
        padding: 0 12px 12px;
        margin: 0;
    }
</style>
<div class="wrap lektrail-settings">
    <h1><?php echo esc_html__('LekTrail Settings', 'lektrail'); ?></h1>

    <form method="post" action="options.php">
        <?php settings_fields(\LekTrail\AdminPage::MENU_SLUG); ?>

        <div class="postbox">
            <h2 class="hndle"><?php echo esc_html__('General', 'lektrail'); ?></h2>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Post Types to Track', 'lektrail'); ?></th>
                        <td>
                            <?php foreach (get_post_types(['public' => true], 'objects') as $lektrail_post_type): ?>
                                <label>
                                    <input type="checkbox"
                                           name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[post_types][]"
                                           value="<?php echo esc_attr($lektrail_post_type->name); ?>"
                                           <?php checked(in_array($lektrail_post_type->name, $pluginConfig->postTypes())); ?>>
                                    <?php echo esc_html($lektrail_post_type->label); ?>
                                </label><br>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Track Logged-In Users', 'lektrail'); ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[track_logged_in_users]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[track_logged_in_users]"
                                       value="1"
                                       <?php checked($pluginConfig->trackLoggedInUsers()); ?>>
                                <?php echo esc_html__('Store reading history in database for logged-in users', 'lektrail'); ?>
                            </label>
                            <p class="description"><?php echo esc_html__('Useful for membership sites. Stores reading history in database for WordPress users (admins, members). Most sites without public registration can leave this disabled.', 'lektrail'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?php echo esc_html__('Display', 'lektrail'); ?></h2>
            <div class="inside">
                <p class="description"><?php echo esc_html__('Control what information is shown for each post in the widget.', 'lektrail'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Show Excerpt', 'lektrail'); ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[show_excerpt]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[show_excerpt]"
                                       value="1"
                                       <?php checked($pluginConfig->showExcerpt()); ?>>
                                <?php echo esc_html__('Display post excerpt below the title', 'lektrail'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Show Thumbnail', 'lektrail'); ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[show_thumbnail]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[show_thumbnail]"
                                       value="1"
                                       <?php checked($pluginConfig->showThumbnail()); ?>>
                                <?php echo esc_html__('Display post thumbnail image', 'lektrail'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Excerpt Length', 'lektrail'); ?></th>
                        <td>
                            <input type="number"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[excerpt_length]"
                                   value="<?php echo esc_attr($pluginConfig->excerptLength()); ?>"
                                   min="5" max="100" class="small-text">
                            <?php echo esc_html__('words', 'lektrail'); ?>
                            <p class="description"><?php echo esc_html__('Number of words to show in excerpt.', 'lektrail'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?php echo esc_html__('Consent Settings', 'lektrail'); ?></h2>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Require Consent', 'lektrail'); ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[require_consent]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[require_consent]"
                                       value="1"
                                       <?php checked($pluginConfig->requireConsent()); ?>>
                                <?php echo esc_html__('Ask users for consent before tracking', 'lektrail'); ?>
                            </label>
                            <p class="description"><?php echo esc_html__('If enabled, users must opt-in before their reading progress is tracked.', 'lektrail'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Consent Message', 'lektrail'); ?></th>
                        <td>
                            <textarea name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[consent_message]"
                                      rows="2" class="large-text"><?php echo esc_textarea($pluginConfig->consentMessage()); ?></textarea>
                            <p class="description"><?php echo esc_html__('Message shown to users when asking for consent.', 'lektrail'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Checkbox Label', 'lektrail'); ?></th>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[consent_checkbox_label]"
                                   value="<?php echo esc_attr($pluginConfig->consentCheckboxLabel()); ?>"
                                   class="regular-text">
                            <p class="description"><?php echo esc_html__('Label for the consent checkbox.', 'lektrail'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?php echo esc_html__('Viewed Section', 'lektrail'); ?></h2>
            <div class="inside">
                <p class="description"><?php echo esc_html__('Posts the visitor started but did not scroll to the end.', 'lektrail'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Enable', 'lektrail'); ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[viewed_enabled]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[viewed_enabled]"
                                       value="1"
                                       <?php checked($pluginConfig->viewedEnabled()); ?>>
                                <?php echo esc_html__('Show this section in the widget', 'lektrail'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Max Posts', 'lektrail'); ?></th>
                        <td>
                            <input type="number"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[max_viewed]"
                                   value="<?php echo esc_attr($pluginConfig->maxViewed()); ?>"
                                   min="1" max="20" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Label', 'lektrail'); ?></th>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[label_continue]"
                                   value="<?php echo esc_attr($pluginConfig->labelContinue()); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?php echo esc_html__('Completed Section', 'lektrail'); ?></h2>
            <div class="inside">
                <p class="description"><?php echo esc_html__('Posts the visitor scrolled to the end.', 'lektrail'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Enable', 'lektrail'); ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[completed_enabled]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[completed_enabled]"
                                       value="1"
                                       <?php checked($pluginConfig->completedEnabled()); ?>>
                                <?php echo esc_html__('Show this section in the widget', 'lektrail'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Read Threshold', 'lektrail'); ?></th>
                        <td>
                            <input type="number"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[read_threshold]"
                                   value="<?php echo esc_attr($pluginConfig->readThreshold()); ?>"
                                   min="10" max="100" class="small-text">
                            <?php echo esc_html('%', 'lektrail'); ?>
                            <p class="description"><?php echo esc_html__('How far the user must scroll to mark the post as completed. Use lower values (e.g., 10%) for product pages where viewing is enough.', 'lektrail'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Max Posts', 'lektrail'); ?></th>
                        <td>
                            <input type="number"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[max_read]"
                                   value="<?php echo esc_attr($pluginConfig->maxRead()); ?>"
                                   min="1" max="20" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Label', 'lektrail'); ?></th>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[label_completed]"
                                   value="<?php echo esc_attr($pluginConfig->labelCompleted()); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?php echo esc_html__('Suggestions Section', 'lektrail'); ?></h2>
            <div class="inside">
                <p class="description"><?php echo esc_html__('Posts the visitor has not seen yet.', 'lektrail'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Max Posts', 'lektrail'); ?></th>
                        <td>
                            <input type="number"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[max_suggestions]"
                                   value="<?php echo esc_attr($pluginConfig->maxSuggestions()); ?>"
                                   min="1" max="20" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Label', 'lektrail'); ?></th>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[label_suggestions]"
                                   value="<?php echo esc_attr($pluginConfig->labelSuggestions()); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Cache Duration', 'lektrail'); ?></th>
                        <td>
                            <input type="number"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[suggestions_cache_hours]"
                                   value="<?php echo esc_attr($pluginConfig->suggestionsCacheHours()); ?>"
                                   min="1" max="168" class="small-text">
                            <?php echo esc_html__('hours', 'lektrail'); ?>
                            <p class="description"><?php echo esc_html__('How long to cache suggestions before refreshing. Also refreshes when you complete reading a post.', 'lektrail'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Order', 'lektrail'); ?></th>
                        <td>
                            <select name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[suggestion_order]">
                                <option value="random" <?php selected($pluginConfig->suggestionOrder(), 'random'); ?>>
                                    <?php echo esc_html__('Random', 'lektrail'); ?>
                                </option>
                                <option value="recent" <?php selected($pluginConfig->suggestionOrder(), 'recent'); ?>>
                                    <?php echo esc_html__('Recent (newest first)', 'lektrail'); ?>
                                </option>
                                <option value="related" <?php selected($pluginConfig->suggestionOrder(), 'related'); ?>>
                                    <?php echo esc_html__('Related (same categories as read posts)', 'lektrail'); ?>
                                </option>
                            </select>
                            <p class="description"><?php echo esc_html__('How to order suggested posts.', 'lektrail'); ?></p>
                        </td>
                    </tr>
                    <?php $lektrail_categories = get_categories(['hide_empty' => false]); ?>
                    <?php if (!empty($lektrail_categories)): ?>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Include Categories', 'lektrail'); ?></th>
                        <td>
                            <fieldset>
                                <?php foreach ($lektrail_categories as $lektrail_category): ?>
                                    <label>
                                        <input type="checkbox"
                                               name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[include_categories][]"
                                               value="<?php echo esc_attr($lektrail_category->term_id); ?>"
                                               <?php checked(in_array($lektrail_category->term_id, $pluginConfig->includeCategories())); ?>>
                                        <?php echo esc_html($lektrail_category->name); ?>
                                    </label><br>
                                <?php endforeach; ?>
                            </fieldset>
                            <p class="description"><?php echo esc_html__('Only suggest posts from these categories. Leave empty to include all. Ignored when using "Related" order.', 'lektrail'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Exclude Categories', 'lektrail'); ?></th>
                        <td>
                            <fieldset>
                                <?php foreach ($lektrail_categories as $lektrail_category): ?>
                                    <label>
                                        <input type="checkbox"
                                               name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[exclude_categories][]"
                                               value="<?php echo esc_attr($lektrail_category->term_id); ?>"
                                               <?php checked(in_array($lektrail_category->term_id, $pluginConfig->excludeCategories())); ?>>
                                        <?php echo esc_html($lektrail_category->name); ?>
                                    </label><br>
                                <?php endforeach; ?>
                            </fieldset>
                            <p class="description"><?php echo esc_html__('Never suggest posts from these categories. Ignored when using "Related" order.', 'lektrail'); ?></p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?php echo esc_html__('Other Labels', 'lektrail'); ?></h2>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Empty State', 'lektrail'); ?></th>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[label_empty]"
                                   value="<?php echo esc_attr($pluginConfig->labelEmpty()); ?>"
                                   class="regular-text">
                            <p class="description"><?php echo esc_html__('Shown when there is nothing to display.', 'lektrail'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Loading', 'lektrail'); ?></th>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[label_loading]"
                                   value="<?php echo esc_attr($pluginConfig->labelLoading()); ?>"
                                   class="regular-text">
                            <p class="description"><?php echo esc_html__('Shown while fetching suggestions.', 'lektrail'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?php echo esc_html__('Clear Data', 'lektrail'); ?></h2>
            <div class="inside">
                <p class="description"><?php echo esc_html__('Allow users to clear their reading history.', 'lektrail'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Show Clear Button', 'lektrail'); ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[show_clear_button]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[show_clear_button]"
                                       value="1"
                                       <?php checked($pluginConfig->showClearButton()); ?>>
                                <?php echo esc_html__('Display a button to clear reading history (stored in browser)', 'lektrail'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Button Label', 'lektrail'); ?></th>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr(\LekTrail\WordPress\PluginConfigRepository::OPTION_KEY); ?>[label_clear]"
                                   value="<?php echo esc_attr($pluginConfig->labelClear()); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <?php submit_button(); ?>
    </form>
</div>