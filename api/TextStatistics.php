<?php
/**
 * Text Statistics Calculator
 * Implements Flesch-Kincaid readability and grade level calculations
 */

class TextStatistics {
    
    /**
     * Calculate Flesch-Kincaid readability score
     * 
     * Formula: 206.835 - (1.015 * ASL) - (84.6 * ASW)
     * Where:
     * - ASL = Average Sentence Length (total words / total sentences)
     * - ASW = Average Syllables per Word (total syllables / total words)
     * 
     * @param string $text
     * @return float Readability score (0-100)
     */
    public static function calculateReadabilityScore($text) {
        $stats = self::getTextStatistics($text);
        
        // Handle edge cases
        if ($stats['sentenceCount'] == 0 || $stats['wordCount'] == 0) {
            return 0;
        }
        
        $asl = $stats['wordCount'] / $stats['sentenceCount']; // Average Sentence Length
        $asw = $stats['syllableCount'] / $stats['wordCount']; // Average Syllables per Word
        
        $score = 206.835 - (1.015 * $asl) - (84.6 * $asw);
        
        // Clamp score between 0 and 100
        return max(0, min(100, $score));
    }
    
    /**
     * Calculate Flesch-Kincaid grade level
     * 
     * Formula: (0.39 * ASL) + (11.8 * ASW) - 15.59
     * Where:
     * - ASL = Average Sentence Length (total words / total sentences)
     * - ASW = Average Syllables per Word (total syllables / total words)
     * 
     * @param string $text
     * @return float Grade level (0-20)
     */
    public static function calculateGradeLevel($text) {
        $stats = self::getTextStatistics($text);
        
        // Handle edge cases
        if ($stats['sentenceCount'] == 0 || $stats['wordCount'] == 0) {
            return 0;
        }
        
        $asl = $stats['wordCount'] / $stats['sentenceCount']; // Average Sentence Length
        $asw = $stats['syllableCount'] / $stats['wordCount']; // Average Syllables per Word
        
        $gradeLevel = (0.39 * $asl) + (11.8 * $asw) - 15.59;
        
        // Clamp grade level between 0 and 20
        // For very complex text, we'll cap it at 20 (post-graduate level)
        return max(0, min(20, $gradeLevel));
    }
    
    /**
     * Get text statistics including word count, sentence count, and syllable count
     * 
     * @param string $text
     * @return array
     */
    public static function getTextStatistics($text) {
        // Clean up text
        $text = trim($text);
        
        // Handle empty text
        if (empty($text)) {
            return [
                'sentenceCount' => 0,
                'wordCount' => 0,
                'syllableCount' => 0
            ];
        }
        
        // Count sentences
        $sentenceCount = self::countSentences($text);
        
        // Count words
        $wordCount = self::countWords($text);
        
        // Count syllables
        $syllableCount = self::countSyllables($text);
        
        return [
            'sentenceCount' => $sentenceCount,
            'wordCount' => $wordCount,
            'syllableCount' => $syllableCount
        ];
    }
    
    /**
     * Count sentences in text
     * 
     * @param string $text
     * @return int
     */
    private static function countSentences($text) {
        // Split by sentence-ending punctuation
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        // Filter out empty sentences and whitespace-only sentences
        $sentences = array_filter($sentences, function($sentence) {
            return trim($sentence) !== '';
        });
        
        return count($sentences);
    }
    
    /**
     * Count words in text
     * 
     * @param string $text
     * @return int
     */
    private static function countWords($text) {
        // Split by whitespace and filter out empty strings
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        // Filter out empty words
        $words = array_filter($words, function($word) {
            return trim($word) !== '';
        });
        
        return count($words);
    }
    
    /**
     * Count syllables in text
     * 
     * @param string $text
     * @return int
     */
    private static function countSyllables($text) {
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $syllableCount = 0;
        
        foreach ($words as $word) {
            $word = trim($word);
            if ($word !== '') {
                $syllableCount += self::countSyllablesInWord($word);
            }
        }
        
        return $syllableCount;
    }
    
    /**
     * Count syllables in a single word
     * 
     * @param string $word
     * @return int
     */
    private static function countSyllablesInWord($word) {
        $word = strtolower(preg_replace('/[^a-zA-Z]/', '', $word)); // Remove non-alphabetic characters
        
        // Special case for empty words
        if (strlen($word) == 0) {
            return 0;
        }
        
        // Special case for single character words
        if (strlen($word) == 1) {
            // Vowels are 1 syllable, consonants are 0 (but we'll count as 1 to avoid 0 syllables)
            return in_array($word, ['a', 'e', 'i', 'o', 'u', 'y']) ? 1 : 1;
        }
        
        // Remove trailing 'e' if it's the last letter
        if (substr($word, -1) === 'e') {
            $word = substr($word, 0, -1);
        }
        
        // Count vowel groups
        preg_match_all('/[aeiouy]+/', $word, $matches);
        $vowelGroups = count($matches[0]);
        
        // At least one syllable for non-empty words
        $syllableCount = max(1, $vowelGroups);
        
        // Handle special cases
        // Words ending in 'le' or 'les' after a consonant
        if (strlen($word) >= 2 && substr($word, -2) === 'le' && 
            !in_array(substr($word, -3, 1), ['a', 'e', 'i', 'o', 'u', 'y'])) {
            $syllableCount++;
        }
        
        return $syllableCount;
    }
}