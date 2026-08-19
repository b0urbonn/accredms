<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Category;
use App\Models\Parameter;
use App\Models\ParameterCategory;
use App\Models\Subfolder;
use App\Models\User;
use Illuminate\Database\Seeder;

class AaccupHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        if (!$admin) return;

        // Ensure Area I exists
        $area = Area::firstOrCreate(
            ['code' => 'AREA-I'],
            [
                'name' => 'VISION, MISSION, GOALS AND OBJECTIVES',
                'description' => 'Dissemination, acceptability, and alignment of institutional vision, college goals, and program objectives.',
                'status' => 'active',
                'created_by' => $admin->id,
            ]
        );

        // Ensure Parameter A exists
        $parameter = Parameter::firstOrCreate(
            ['area_id' => $area->id, 'code' => '1.1'],
            [
                'title' => 'STATEMENT OF VISION, MISSION, GOALS AND OBJECTIVES',
                'description' => 'System of formulation, dissemination, and evaluation of institutional VMGO.',
                'sort_order' => 1,
            ]
        );

        // Get ParameterCategories for Parameter 1.1
        $catSystem = Category::where('name', 'System Input and Process')->first();
        $catImpl = Category::where('name', 'Implementation')->first();
        $catOutcomes = Category::where('name', 'Outcomes')->first();

        $paramCatSystem = ParameterCategory::where('parameter_id', $parameter->id)->where('category_id', $catSystem->id)->first();
        $paramCatImpl = ParameterCategory::where('parameter_id', $parameter->id)->where('category_id', $catImpl->id)->first();
        $paramCatOutcomes = ParameterCategory::where('parameter_id', $parameter->id)->where('category_id', $catOutcomes->id)->first();

        // Seed Subfolders for SYSTEM - INPUTS AND PROCESSES
        if ($paramCatSystem) {
            $systemItems = [
                [
                    'code' => 'S.1',
                    'name' => 'The institution has a system of determining the Vision and Mission.',
                    'documents_needed' => "• Notices of Meeting/Invitation Letters, re: formulation of VM with proof of receipt of concerned stakeholders\n• Minutes of the Meetings/Report of the Proceedings with actual attendance\n• Action Photos of the proceedings with caption\n• BOR approval of University Vision and Mission",
                ],
                [
                    'code' => 'S.2',
                    'name' => 'The Vision clearly reflects what the Institution hopes to become in the future.',
                    'documents_needed' => "• University Vision (UV) Statement\n• Narrative to describe the institution's hopes to become in the future",
                ],
                [
                    'code' => 'S.3',
                    'name' => 'The Mission clearly reflects the Institution\'s legal and other statutory mandates.',
                    'documents_needed' => "• University Mission (UM) Statement\n• Development Plan\n• Charter",
                ],
                [
                    'code' => 'S.4',
                    'name' => 'The Goals of the College/Academic Unit is consistent with the Mission of the institution.',
                    'documents_needed' => "• Statement of the College Goals and University Mission (UM)\n• Matrix of comparison of college goals and university mission",
                ],
            ];

            foreach ($systemItems as $item) {
                Subfolder::firstOrCreate(
                    [
                        'parameter_category_id' => $paramCatSystem->id,
                        'code' => $item['code'],
                    ],
                    [
                        'name' => $item['name'],
                        'documents_needed' => $item['documents_needed'],
                        'created_by' => $admin->id,
                        'status' => 'active',
                    ]
                );
            }

            // Parent S.5 Subfolder
            $parentS5 = Subfolder::firstOrCreate(
                [
                    'parameter_category_id' => $paramCatSystem->id,
                    'code' => 'S.5',
                ],
                [
                    'name' => 'The Objectives of the program have the expected outcomes in terms of competencies, values and attributes.',
                    'documents_needed' => "• Statement of the Objectives of the Academic Program\n• Matrix of competencies, values and other attributes of the graduates against the objectives of the program",
                    'created_by' => $admin->id,
                    'status' => 'active',
                ]
            );

            // Nested Child Sub-items under S.5
            $s5Children = [
                [
                    'code' => 'S.5.1',
                    'name' => 'Technical / pedagogical skills competencies',
                    'documents_needed' => "• Statement of Objectives for Technical & Pedagogical Skills\n• Matrix of technical competencies against program objectives",
                ],
                [
                    'code' => 'S.5.2',
                    'name' => 'Research and extension capabilities',
                    'documents_needed' => "• Research & Extension Capability Framework\n• Student & Faculty research outputs matrix",
                ],
                [
                    'code' => 'S.5.3',
                    'name' => 'Students\' own ideas, desirable attitudes and personal discipline',
                    'documents_needed' => "• Student Handbook Code of Discipline\n• Student project proposals and attitude evaluation rubrics",
                ],
                [
                    'code' => 'S.5.4',
                    'name' => 'Moral character',
                    'documents_needed' => "• Values Formation Syllabus & Modules\n• Ethics & Good Governance seminar documentation",
                ],
                [
                    'code' => 'S.5.5',
                    'name' => 'Critical, analytical, problem solving and higher order thinking skills',
                    'documents_needed' => "• Capstone / Thesis Assessment Rubrics\n• Higher Order Thinking Skills (HOTS) evaluation instruments",
                ],
                [
                    'code' => 'S.5.6',
                    'name' => 'Aesthetic and cultural values',
                    'documents_needed' => "• Cultural & Arts Program Documentation\n• Humanities and Aesthetics course portfolio",
                ],
            ];

            foreach ($s5Children as $child) {
                Subfolder::firstOrCreate(
                    [
                        'parameter_category_id' => $paramCatSystem->id,
                        'parent_id' => $parentS5->id,
                        'code' => $child['code'],
                    ],
                    [
                        'name' => $child['name'],
                        'documents_needed' => $child['documents_needed'],
                        'created_by' => $admin->id,
                        'status' => 'active',
                    ]
                );
            }
        }

        // Seed Subfolders for IMPLEMENTATION
        if ($paramCatImpl) {
            $implItems = [
                [
                    'code' => 'I.1',
                    'name' => 'The Institution/College conducts a review on the statements of the Vision and Mission as well as its goals and program objectives for the approval of authorities concerned.',
                    'documents_needed' => "• University Notices of Meetings re Review of VM, Dean/Director notices of meetings re review of College goals and program objectives with proof of dissemination and receipt\n• Minutes of Meetings with actual attendance\n• Action photos of proceedings\n• Guidelines on Review of VMGO with proof of dissemination",
                ],
            ];

            foreach ($implItems as $item) {
                Subfolder::firstOrCreate(
                    [
                        'parameter_category_id' => $paramCatImpl->id,
                        'code' => $item['code'],
                    ],
                    [
                        'name' => $item['name'],
                        'documents_needed' => $item['documents_needed'],
                        'created_by' => $admin->id,
                        'status' => 'active',
                    ]
                );
            }
        }

        // Seed Subfolders for OUTCOMES
        if ($paramCatOutcomes) {
            $outcomeItems = [
                [
                    'code' => 'O.1',
                    'name' => 'The VMGO are widely disseminated to all stakeholders and published in institutional media.',
                    'documents_needed' => "• Survey results on VMGO awareness and acceptability\n• Published VMGO posters, student handbook, website screenshots, and orientation photos",
                ],
            ];

            foreach ($outcomeItems as $item) {
                Subfolder::firstOrCreate(
                    [
                        'parameter_category_id' => $paramCatOutcomes->id,
                        'code' => $item['code'],
                    ],
                    [
                        'name' => $item['name'],
                        'documents_needed' => $item['documents_needed'],
                        'created_by' => $admin->id,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
