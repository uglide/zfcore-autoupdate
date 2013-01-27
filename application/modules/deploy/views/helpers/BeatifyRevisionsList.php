<?php
/**
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * BeatifyRevisionsList.php
 * Date: 21.12.12
 */
class Deploy_View_Helper_BeatifyRevisionsList extends Zend_View_Helper_Abstract
{
    /**
     * @param $revisions
     * @param $tags
     *
     * @return array
     */
    public function beatifyRevisionsList($revisions, $tags)
    {
        if (!count($revisions)) {
            return array();
        }

        if (!count($tags)) {
            return array_combine($revisions, $revisions);
        }

        $revisionsLabels = array();

        foreach ($revisions as $rev) {
            if (array_key_exists($rev, $tags)) {
                $revisionsLabels[] = $tags[$rev];
            } else {
                $revisionsLabels[] = $rev;
            }
        }

        return array_combine($revisions, $revisionsLabels);
    }
}
