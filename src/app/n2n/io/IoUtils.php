<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\io;

use n2n\io\fs\FileResourceStream;
use n2n\io\fs\FileOperationException;

class IoUtils {
	
// 	public static function hasFsSpecialChars($string) {
// 		return false === strpbrk($string, '<>:"/\|?*') && ctype_print($string)
// 				&& !StringUtils::isEmpty($string);
// 	}
	/**
 	 * strips and converts characters of a string so it can be enbedded into urls
 	 * or file paths without any encoding nessesary 
 	 *
 	 * @param string $string string to be cleaned
 	 * @return string
 	 */
	public static function stripSpecialChars($string, $pretty = true) {
		$string = trim($string);
		
		$unwanted = array('ä', 'à', 'â', 'ç', 'é', 'è', 'ê', 'î', 'ö', 'ß', 'ü',
				'Ä', 'À', 'Â', 'É', 'È', 'Ê', 'Î', 'Ö','Ü');
		$wanted = array('ae', 'a', 'a', 'c', 'e', 'e', 'e', 'i', 'oe', 'ss', 'ue',
				'Ae', 'A', 'A', 'E', 'E', 'E', 'I', 'Oe','Ue');
		$string = str_replace($unwanted, $wanted, $string);
	
		$string = preg_replace('/\s/s', '-', $string);
		$string = preg_replace('/[^0-9A-Za-z\\._-]/', '', $string);
		
		if ($pretty) {
			$string = preg_replace('/-{2,}/', '-', $string);
			$string = preg_replace('/-?\\.-?/', '.', $string);
			$string = preg_replace('/-?_-?/', '_', $string);
			$string = preg_replace('/\\.{2,}/', '.', $string);
			$string = preg_replace('/_{2,}/', '_', $string);
			$string = preg_replace('/[-,_\\.]+$/', '', $string);
			$string = preg_replace('/^[-,_\\.]+/', '', $string);
		}
		
		if ($string == '.') {
			$string = '';
		}
	
		return $string;
	}
 	/**
 	 * 
 	 * @param string $string
 	 * @return bool
 	 */
 	public static function hasSpecialChars($string) {
 		return preg_match('/[^0-9A-Za-z\\._-]/', $string) || $string == '.' || $string == '..';
 	}
 	
 	/**
 	 * @param string $string
 	 * @return boolean
 	 */
 	public static function hasStrictSpecialChars($string) {
 		return (boolean) preg_match('/\W/', $string);
 	}
 	
 	/**
 	 * @param string $string
 	 * @return string
 	 */
 	public static function replaceStrictSpecialChars($string) {
 		return preg_replace('/\W/', '_', $string);
 	}
	/**
	 * 
	 * @param string $oldPath
	 * @param string $newPath
	 * @throws IoException
	 */
	public static function rename($oldPath, $newPath) {
		try {
			rename($oldPath, $newPath);
		} catch(\Throwable $e) {
			throw new FileOperationException('Rename of \'' . $oldPath . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	
	/**
	 * @param string $path
	 * @param string $permission
	 * @throws IoException
	 */
	public static function mkdirs($path, $permission) {
		try {
			mkdir($path, octdec($permission), true);
		} catch(\Throwable $e) {
			throw new FileOperationException('Mkdir of \'' . $path . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	
	public static function rmdir($path) {
		try {
			rmdir($path);
		} catch(\Throwable $e) {
			throw new FileOperationException('Rmdir of \'' . $path . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	/**
	 * 
	 * @param string $path
	 * @throws IoException
	 */
	public static function rmdirs(string $path) {
		if (is_dir($path)) {
			try {
				$handle = opendir($path);
			} catch(\Throwable $e) {
				throw new IoException('Opendir of \'' . $path . '\' failed. Reason: ' . $e->getMessage());
			}
			
			while (false !== ($fileName = readdir($handle))) {
				if ($fileName == '.' || $fileName == '..') continue;
				
				self::rmdirs($path . DIRECTORY_SEPARATOR . $fileName);
			}
		
			closedir($handle);
			// @todo check requirements
			clearstatcache();		
			IoUtils::rmdir($path);
		} else if (is_file($path)) {
			IoUtils::chmod($path, '0777');
			IoUtils::unlink($path);
		}
	}
	
	/**
	 * @param string $path
	 * @param resource $context
	 * @throws IoException
	 * @return boolean
	 */
	public static function opendir(string $path, $context = null) {
		$h = null;
		try {
			if ($context === null) {
				$h = opendir($path);
			} else {
				$h = opendir($path, $context);
			}
		} catch(\Throwable $e) {
			throw new IoException('Opendir of \'' . $path . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	
	/**
	 * 
	 * @param string $pattern
	 * @param string $flags
	 * @return array
	 */
	public static function glob($pattern, $flags = 0) {
		$paths = glob($pattern, $flags);

		// Return array on false due to different behaviour on different systems
		if (!is_array($paths)) {
			return array();
		}
		
		return $paths;
	}
	/**
	 * 
	 * @param string $path
	 * @param string $filePermission
	 * @throws IoException
	 * @deprecated use IoUtils::touch() and IoUtils::chmod()
	 */
	public static function createFile($path, $filePermission = null) {
		IoUtils::touch($path);
		if (isset($filePermission)) {
			IoUtils::chmod($path, $filePermission);
		}
	}
	/**
	 * 
	 * @param string $path
	 * @param string $contents
	 * @param int $flags
	 * @param resource $context
	 * @throws IoException
	 */
	public static function putContents(string $path, $contents, $flags = null, $context = null) {
		try {
			file_put_contents((string) $path, $contents, $flags, $context);
		} catch (\Throwable $e) {
			throw new FileOperationException('PutContents of \'' . $path . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	/**
	 * 
	 * @param string $path
	 * @return string
	 * @throws IoException
	 */
	public static function getContents(string $path) {
		try {
			return file_get_contents($path);
		} catch (\Throwable $e) {
			throw new FileOperationException('GetContents of \'' . $path . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	/**
	 * 
	 * @param string $path
	 * @return string
	 * @throws IoException
	 */
	public static function file(string $path) {
		try {
			return file($path);
		} catch (\Throwable $e) {
			throw new FileOperationException('File of \'' . $path . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	/**
	 * 
	 * @param string $path
	 * @param string $targetPath
	 * @throws IoException
	 */
	public static function copy($path, $targetPath, $context = null) {
		try {
			if ($context === null) {
				if (copy($path, $targetPath)) return;
			} else {
				if (copy($path, $targetPath, $context)) return;
			}
		} catch (\Throwable $e) {
			throw new FileOperationException('Copy of \'' . $path . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	/**
	 * 
	 * @param string $path
	 * @param string $filePermission octal string
	 * @throws IoException
	 */
	public static function chmod($path, $filePermission) {
		if (is_string($filePermission)) {
			$filePermission = octdec($filePermission);
		}

		try {
			$result = chmod($path, $filePermission);
		} catch (\Throwable $e) {
			throw new FileOperationException('Chmod of \'' . $path . '\' (permission: ' . $filePermission . ') 
					failed. Reason: ' . $e->getMessage());
		}

		if ($result === false) {
			throw new FileOperationException('Chmod of \'' . $path . '\' (permission: ' . $filePermission . ') 
					failed.');
		}
	}

	/**
	 * @todo add param time and atime  
	 */
	public static function touch($filename) {
		try {
			touch($filename);
		} catch (\Throwable $e) {
			throw new FileOperationException('Touch of \'' . $filename . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	/**
	 * 
	 * @param string $path
	 * @param string $mode
	 * @throws IoException
	 * @return resource
	 */
	public static function fopen(string $path, string $mode) {
		try {
			return fopen($path, $mode);
		} catch (\Throwable $e) {
			throw new IoException('Fopen of \'' . $path . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	
	public static function stat(string $path) {
		try {
			return stat($path);
		} catch (\Throwable $e) {
			throw new FileOperationException('Stat of \'' . $path . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	
	public static function filesize(string $path) {
		try {
			return filesize($path);
		} catch (\Throwable $e) {
			throw new FileOperationException('Filesize of \'' . $path . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	
	public static function readfile(string $path) {
		try {
			return readfile($path);
		} catch (\Throwable $e) {
			throw new FileOperationException('Readfile of \'' . $path . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	
	public static function fwrite($handle, $string) {
		$num = fwrite($handle, $string);
		if (false === $num) {
			throw new IoResourceException('Could not write to resource: ' . $handle);
		}
		
		return $num;
	}
	
	public static function fread($handle, int $length = null) {
		$str = fread($handle, $length);
		if (false === $str) {
			throw new IoResourceException('Could not read from file');
		}
		
		return $str;
	}
	
	public static function fgets($handle, int $length = null) {
		$str = fgets($handle, $length);
		if (false === $str) {
			throw new IoResourceException('Could not read from file');
		}
	
		return $str;
	}
	
	public static function streamGetContents($handle, int $maxlength = -1, int $offset = -1) {
		try {
			return stream_get_contents($handle, $maxlength, $offset);
		} catch (\Throwable $e) {
			throw new IoResourceException('Streamgetcontents failed. Reason: ' . $e->getMessage());
		}
	}
	
	public static function createSafeFileStream(string $filePath) {
		return new FileResourceStream($filePath, 'c+', LOCK_EX);
	}
	/**
	 * 
	 * @return FileResourceStream
	 */
	public static function createSafeFileOutputStream(string $filePath) {
		return new FileResourceStream($filePath, 'w', LOCK_EX);
	}
	/**
	 * 
	 * @param string $filePath
	 * @return FileResourceStream
	 */
	public static function createSafeFileInputStream(string $filePath) {
		return new FileResourceStream($filePath, 'r', LOCK_SH);
	}
	/**
	 * 
	 * @param string $path
	 * @param string $contents
	 * @throws IoException
	 */
	public static function putContentsSafe(string $path, string $contents) {
		$fileOutputStream = self::createSafeFileOutputStream($path);
		$fileOutputStream->write($contents);
		$fileOutputStream->close();
	}
	/**
	 * 
	 * @param string $path
	 * @return string
	 * @throws IoException
	 */
	public static function getContentsSafe($path) {
		$fileReader = self::createSafeFileInputStream($path);
		$contents = $fileReader->read();
		$fileReader->close();
		return $contents;
	}
	/**
	 * 
	 * @param string $path
	 * @return string
	 * @throws IoException
	 */
	public static function filemtime($path) {
		try {
			return filemtime($path);;
		} catch (\Throwable $e) {
			throw new FileOperationException('Filemtime of \'' . $path . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	/**
	 * 
	 * @param string $path
	 * @throws IoException
	 */
	public static function unlink($path) {
		try {
			unlink($path);
		} catch (\Throwable $e) {
			throw new FileOperationException('Unlink of \'' . $path . '\' failed. Reason: ' . $e->getMessage());
		}
	}	
	/**
	 * 
	 * @param string $iniString
	 * @param string $processSections
	 * @param string $scannerMode
	 * @throws IoException
	 * @return array
	 */
	public static function parseIniString($iniString, $processSections = false, $scannerMode = null) {
		try {
			return parse_ini_string($iniString, $processSections, $scannerMode);
		} catch (\Throwable $e) {
			throw new IoException('ParseIniString of \'' . $path . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	/**
	 * 
	 * @param string $path
	 * @param string $processSections
	 * @param string $scannerMode
	 * @throws IoException
	 * @return array
	 */
	public static function parseIniFile($path, $processSections = false, $scannerMode = null) {
		try {
			return parse_ini_file($path, $processSections, $scannerMode);
		} catch (\Throwable $e) {
			throw new IoException('ParseIniFile of \'' . $path . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	
	public static function imageCreateFromPng($filePath) {
		try {
			return imagecreatefrompng($filePath);
		} catch (\Throwable $e) {
			throw new IoException('Imagecreatefrompng of \'' . $filePath . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	
	public static function imageCreateFromGif($filePath) {
		try {
			return imagecreatefromgif($filePath);
		} catch (\Throwable $e) {
			throw new IoException('Imagecreatefromgif of \'' . $filePath . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	
	public static function imageCreateFromJpeg($filePath) {
		try {
			return imagecreatefromjpeg($filePath);
		} catch (\Throwable $e) {
			throw new IoException('Imagecreatefromjpeg of \'' . $filePath . '\' failed. Reason: ' . $e->getMessage());
		}
	}
		
		
	public static function imageCreateFromWebp($filePath) {
		try {
			return imagecreatefromwebp($filePath);
		} catch (\Throwable $e) {
			throw new IoException('Imagecreatefromwebp of \'' . $filePath . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	
	public static function imagePng($resource, $filePath = null, $quality = null, $filters = null) {
		try {
			return imagepng($resource, $filePath, $quality, $filters);
		} catch (\Throwable $e) {
			throw new IoException('Imagepng of \'' . $filePath . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	
	public static function imageGif($resource, $filePath = null) {
		try {
			return imagegif($resource, $filePath);
		} catch (\Throwable $e) {
			throw new IoException('Imagegif of \'' . $filePath . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	
	public static function imageJpeg($resource, $filePath = null, $quality = null) {
		try {
			return imagejpeg($resource, $filePath);
		} catch (\Throwable $e) {
			throw new IoException('Imagejpeg of \'' . $filePath . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	
	public static function imageWebp($resource, $filePath = null, $quality = null) {
		try {
			return imagewebp($resource, $filePath);
		} catch (\Throwable $e) {
			throw new IoException('Imagejpeg of \'' . $filePath . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	
	/**
	 * 
	 * @param string $filePath
	 * @param int $operation
	 * @return Flock
	 */
	public static function createFlock($filePath, $operation, $requried = true) {
		try {
			return new Flock(self::fopen($filePath, 'c'), $operation);
		} catch (CouldNotAchieveFlockException $e) {
			if (!$requried) return null;
			throw $e;
		}
	}
	/**
	 * 
	 * @param string $orgPath
	 * @throws InvalidPathException
	 */
	public static function realpath($orgPath, $fileRequired = null) {
		$path = realpath($orgPath);
		
		if ($path === false) {
			throw new InvalidPathException('Path not found:' . $orgPath);
		}
		
		if ($fileRequired === true && !is_file($path)) {
			throw new InvalidPathException('Path points to non file:' . $path);
		}
		
		if ($fileRequired === false && !is_dir($path)) {
			throw new InvalidPathException('Path points to non directory: ' . $path);
		}
		
		return $path;
	}
	
	public static function flock($handle, $operation, &$wouldblock = null) {
		if (!flock($handle, $operation, $wouldblock)) {
			throw new CouldNotAchieveFlockException('Could not achieve flock: ' . $operation);
		}
		return true;
	}
	
	public static function ftruncate($handle, $size) {
		if (!ftruncate($handle, $size)) {
			throw new IoResourceException('Could not truncate.');
		}
	}
	
	public static function fseek($handle, $offset, $whence = SEEK_SET) {
		if (false === fseek($handle, $offset, $whence)) {
			throw new IoResourceException('Could not seek. Offset: ' . $offset);
		}
	}
	
	public static function ftell($handle, $offset, $whence = SEEK_SET) {
		$offset = ftell($handle);
		if (false === $offset) {
			throw new IoResourceException('Could not tell.');
		}
		return $offset;
	}
					
// 	public static function ensureFileIsAccessibleThroughHttp(Managable $file) {
// 		if ($file->getFileManager() instanceof HttpAccessible) return;
// 		throw new FileManagingException(
// 				SysTextUtils::get('n2n_error_io_file_is_not_accessible_through_http',
// 						array('file' => $file->getPath(), 'file_name' => $file->getOriginalName())));
// 	}
	
	/**
	 * @param string $filename
	 * @throws IoException
	 * @return string
	 */
	public static function getimagesize($filename) {
		try {
			return getimagesize($filename);
		} catch (\Throwable $e) {
			throw new IoException('Getimagesize of \'' . $filename . '\' failed. Reason: ' . $e->getMessage());
		}
	}
	/**
	 * Returns a file size limit in bytes based on the PHP upload_max_filesize
	 * and post_max_size
	 * @return int
	 */
	public static function determineFileUploadMaxSize() {
		static $maxSize = -1;
	
		if ($maxSize < 0) {
			// Start with post_max_size.
			$maxSize = self::parsePhpIniSize(ini_get('post_max_size'));
			
			// If upload_max_size is less, then reduce. Except if upload_max_size is
			// zero, which indicates no limit.
			$upload_max = self::parsePhpIniSize(ini_get('upload_max_filesize'));
			if ($upload_max > 0 && $upload_max < $maxSize) {
				$maxSize = $upload_max;
			}
		}
		
		return $maxSize;
	}
	
	const DEFAULT_MEMORY_LIMIT = 33554432;
	
	/**
	 * @return int
	 */
	static function determinMemoryLimit() {
		static $memoryLimit = null;
		
		if ($memoryLimit !== null) {
			return $memoryLimit;
		}
		
		$memoryLimit = self::parsePhpIniSize(ini_get('memory_limit'));
		
		if (empty($memoryLimit)) {
			$memoryLimit = self::DEFAULT_MEMORY_LIMIT;
		}
		
		return $memoryLimit;
	}
	
	
	public static function curlExec($ch) {
		try {
			curl_exec($ch);
		} catch (\Throwable $e) {
			throw new CurlOperationException('Curlexec of \'' . $ch . '\' failed. Reason: ' . $e->getMessage());
		}

		curl_close($ch);
	}
	
	public static function parsePhpIniSize(string $size) {
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
		$size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
		if ($unit) {
			// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
			return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
		} else {
			return round($size);
		}
	}
}
