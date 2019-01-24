<?php
/**
 * Created by Navatech.
 * @project yii2-backup
 * @author  Phuong
 * @email   notteen[at]gmail.com
 * @date    1/22/2019
 * @time    11:49 AM
 */

namespace navatech\backup\helpers;
class FileHelper extends \yii\helpers\FileHelper {

	/**
	 * @param        $size
	 * @param string $unit
	 *
	 * @return string
	 */
	public static function humanFileSize($size, $unit = "") {
		if ((!$unit && $size >= 1 << 30) || $unit == "GB") {
			return number_format($size / (1 << 30), 2) . "GB";
		}
		if ((!$unit && $size >= 1 << 20) || $unit == "MB") {
			return number_format($size / (1 << 20), 2) . "MB";
		}
		if ((!$unit && $size >= 1 << 10) || $unit == "KB") {
			return number_format($size / (1 << 10), 2) . "KB";
		}
		return number_format($size) . " bytes";
	}
}
