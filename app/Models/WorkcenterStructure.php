<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkcenterStructure extends Model
{

    use SoftDeletes;

    protected $table = 'workcenter_structures';

    protected $fillable = [
        'structure_code',
        'structure_name',
        'structure_type',
        'structure_contract',
        'structure_parent_id',
    ];

    public function children()
    {
        return $this->hasMany(WorkcenterStructure::class, 'structure_parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('children.children');
    }

    public function childrenAllRecursive()
    {
        return $this->children()->with('childrenAllRecursive');
    }

    public function parent()
    {
        return $this->belongsTo(WorkcenterStructure::class, 'structure_parent_id');
    }

    public function downtimes()
    {
        return $this->hasMany(WorkcenterDowntime::class, 'workcenter_structure_id');
    }

    public function workinstructionFile()
    {
        return $this->hasOne(WorkcenterFile::class, 'workcenter_structure_id');
    }

    public function fullHierarchyPath($separator = ' << ', $reverse = false)
    {
        $path = [$this->structure_code . ' - ' . $this->structure_name];
        $current = $this->parent;
        $contract = $this->structure_contract;

        if ($current) {
            $contract = $current->structure_contract;
        }

        while ($current) {
            $path[] = $current->structure_name;
            $current = $current->parent;

            if ($current) {
                $contract = $current->structure_contract;
            }
        }

        if (!$reverse) {
            $path = array_reverse($path);
        }

        return [
            'path' => implode($separator, $path),
            'path_contract' => $contract,
        ];
    }

    public function allAncestorDowntimes()
    {
        $downtimes = collect();
        $downtimes = $downtimes->merge($this->downtimes);

        if ($this->parent) {
            $downtimes = $downtimes->merge($this->parent->downtimes);

            if ($this->parent->parent) {
                $downtimes = $downtimes->merge($this->parent->parent->downtimes);
            }
        }

        return $downtimes;
    }

    public function scopeFromNetoWithAncestors($query, $netoId)
    {
        return $query->whereIn('id', function ($sub) use ($netoId) {
            $sub->selectRaw('id')->from('workcenter_structures')->whereIn('id', function ($inner) use ($netoId) {
                $inner->selectRaw('DISTINCT id')
                    ->fromRaw("(
                        SELECT id FROM workcenter_structures WHERE id = ?
                        UNION
                        SELECT structure_parent_id FROM workcenter_structures WHERE id = ?
                        UNION
                        SELECT structure_parent_id FROM workcenter_structures
                            WHERE id = (
                                SELECT structure_parent_id FROM workcenter_structures WHERE id = ?
                            )
                    ) AS ancestors", [$netoId, $netoId, $netoId]);
            });
        });
    }

    public function characteristics()
    {
        return $this->belongsToMany(
            Characteristic::class,
            'workcenter_template',
            'workcenter_structure_id',
            'characteristic_id'
            )->withPivot('id','cols', 'order')->withTimestamps()
            ->wherePivotNull('deleted_at');

    }

    public function getHierarchyContext($separator = ' « ', $reverse = true)
    {
        $this->loadMissing('parent.children', 'parent.parent');

        $collection = collect();

        $grandparent = optional($this->parent)->parent;
        $parent = $this->parent;
        $siblings = $parent ? $parent->children->filter(fn ($child) => $child->id !== $this->id) : collect();

        // if ($grandparent) {
        //     $collection->push([
        //         'ID' => $grandparent->id,
        //         'DESCRIPTION' => $grandparent->fullHierarchyPath($separator, $reverse)['path'],
        //     ]);
        // }

        if ($parent) {
            $collection->push([
                'ID' => $parent->id,
                'DESCRIPTION' => $parent->fullHierarchyPath($separator, $reverse)['path'],
            ]);
        }

        foreach ($siblings as $sibling) {
            $collection->push([
                'ID' => $sibling->id,
                'DESCRIPTION' => $sibling->fullHierarchyPath($separator, $reverse)['path'],
            ]);
        }

        return $collection->values();
    }

    public function getSiblingOrCousinCodes()
        {
            $this->loadMissing('parent.children', 'parent.parent.children.children');

            $codes = collect();

            $parent = $this->parent;
            $grandparent = optional($parent)->parent;

            if (!$parent) {
                return $codes;
            }

            if ($grandparent && $grandparent->departmentOrder == 1) {
                foreach ($grandparent->children as $uncleOrParent) {
                    foreach ($uncleOrParent->children as $cousinOrSibling) {
                        if ($cousinOrSibling->id !== $this->id) {
                            $codes->push($cousinOrSibling->structure_code);
                        }
                    }
                }
            } else {
                foreach ($parent->children as $sibling) {
                    if ($sibling->id !== $this->id) {
                        $codes->push($sibling->structure_code);
                    }
                }
            }

            return $codes->values()->all();
        }

}
