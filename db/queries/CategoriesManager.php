<?php

class CategoriesManager
{
    private $areas_set = [];
    private $categories_set = [];
    private $category_area_map = [];
    private $mysqli;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
        $this->loadCategories();
    }

    private function loadCategories()
    {
        $stmt = $this->mysqli->prepare("SELECT nome_area FROM AREE");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $this->areas_set[$row['nome_area']] = true;
            }
            $stmt->close();
        }

        $stmt = $this->mysqli->prepare("SELECT nome_categoria, nome_area FROM CATEGORIE");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $this->categories_set[$row['nome_categoria']] = true;
                $this->category_area_map[$row['nome_categoria']] = $row['nome_area'];
            }
            $stmt->close();
        }
    }

    public function uploadDefault(): void
    {
        $default_areas = ['Computer Science', 'Other'];
        $cs_categories = [
            'Artificial Intelligence',
            'Computational Theory and Mathematics',
            'Computer Graphics and Computer-Aided Design',
            'Computer Networks and Communications',
            'Computer Science Applications',
            'Computer Science (miscellaneous)',
            'Computer Vision and Pattern Recognition',
            'Hardware and Architecture',
            'Human-Computer Interaction',
            'Information Systems',
            'Signal Processing',
            'Software'
        ];

        foreach ($default_areas as $area) {
            $this->addArea($area);
        }

        foreach ($cs_categories as $category) {
            $this->addCategory($category, 'Computer Science');
        }

        $this->addCategory('Other', 'Other');
    }

    public function categoryExists($category_name)
    {
        return isset($this->categories_set[$category_name]);
    }

    public function getAreaByCategory($category_name)
    {
        return $this->category_area_map[$category_name] ?? 'Other';
    }

    public function addArea(string $area_name): bool
    {
        $stmt = $this->mysqli->prepare("INSERT IGNORE INTO AREE (nome_area) VALUES (?)");
        if (!$stmt) return false;

        $stmt->bind_param("s", $area_name);
        $success = $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        if ($success && $affected_rows > 0) {
            $this->areas_set[$area_name] = true;
            return true;
        }

        return false;
    }

    public function addCategory($category_name, $area_name): bool
    {
        $stmt = $this->mysqli->prepare("INSERT IGNORE INTO CATEGORIE (nome_categoria, nome_area) VALUES (?, ?)");
        if (!$stmt) return false;

        $stmt->bind_param("ss", $category_name, $area_name);
        $success = $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        if ($success && $affected_rows > 0) {
            $this->categories_set[$category_name] = true;
            $this->category_area_map[$category_name] = $area_name;
            return true;
        }

        return false;
    }
}