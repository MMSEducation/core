<?php

namespace Chatter\Core\Helpers;

class ChatterHelper
{
    /**
     * Convert any string to a color code.
     *
     * @param string $string
     *
     * @return string
     */
    public static function stringToColorCode($string)
    {
        $code = dechex(crc32($string));

        return substr($code, 0, 6);
    }

    /**
     * User link.
     *
     * @param mixed $user
     *
     * @return string
     */
    public static function userLink($user)
    {
        $url = config('chatter.user.relative_url_to_profile', '');

        if ('' === $url) {
            return '#_';
        }

        return static::replaceUrlParameter($url, $user);
    }

    /**
     * Replace url parameter.
     *
     * @param string $url
     * @param mixed $source
     *
     * @return string
     */
    private static function replaceUrlParameter($url, $source)
    {
        $parameters = static::urlsParameters($url);

        foreach ($parameters as $parameter){
            $url = str_replace('{' . $parameter . '}', $source->{$parameter}, $url);
        }

        return $url;
    }

    /**
     * Url parameter.
     *
     * @param string $url
     *
     * @return string[]
     */
    private static function urlsParameters($url)
    {
        $processedUrl = $url;
        $parameters = [];
        while(($paramStart = strpos($processedUrl, '{')) !== false){

            $paramEnd = strpos($processedUrl, '}');

            $parameters[] = substr($processedUrl, $paramStart + 1, $paramEnd - $paramStart - 1);
            $processedUrl = substr($processedUrl, $paramEnd + 1);
        }

        return $parameters;
    }

    /**
     * This function will demote H1 to H2, H2 to H3, H4 to H5, etc.
     * this will help with SEO so there are not multiple H1 tags
     * on the same page.
     *
     * @param HTML string
     *
     * @return HTML string
     */
    public static function demoteHtmlHeaderTags($html)
    {
        $originalHeaderTags = [];
        $demotedHeaderTags = [];

        foreach (range(100, 1) as $index) {
            $originalHeaderTags[] = '<h' . $index . '>';

            $originalHeaderTags[] = '</h' . $index . '>';

            $demotedHeaderTags[] = '<h' . ($index + 1) . '>';

            $demotedHeaderTags[] = '</h' . ($index + 1) . '>';
        }

        return str_ireplace($originalHeaderTags, $demotedHeaderTags, $html);
    }

    /**
     * This function construct the categories menu with nested categories.
     *
     * @param array $categories
     *
     * @return string
     */
    public static function categoriesMenu($categories)
    {
        $menu = '<ul class="nav nav-pills nav-stacked">';

        foreach ($categories as $category) {
            $menu .= '<li>';
            $menu .= '<a href="' . route('chatter.category.show', $category['slug']) . '">';
            $menu .= '<div class="chatter-box" style="background-color:' . $category['color'] . '"></div>';
            $menu .= $category['name'] . '</a>';

            if (count($category['parents'])) {
                $menu .= static::categoriesMenu($category['parents']);
            }

            $menu .= '</li>';
        }

        $menu .= '</ul>';

        return $menu;
    }

    /**
     * This function converts id to queryable form.
     *
     * @param string|int $id
     *
     * @return string|int
     */
    public static function toQueryableId($id)
    {
        if (!config('chatter.requires_numeric_database_ids')) {
            return $id;
        }

        return is_numeric($id) ? (int)$id : 0;
    }


     /**
     * This function will check permission of  user to the post 
     *
     * @param string|int $postId
     *
     * @return string|int
     */
    public static function checkPermission($user,$postid)
    {
        return ['canEdit'=>false,'canDelete'=>false];
    }
}
