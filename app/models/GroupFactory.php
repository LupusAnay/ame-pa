<?php


class GroupFactory
{
    /**
     * @param array $data
     * @return Group
     * @throws ValidationError
     */
    public function make(array $data): Group {
        $this->validate_group_data($data);

        $status = array_key_exists(Group::STATUS, $data) ? $data[Group::STATUS] : Group::STATUS_PUBLIC;

        $group = new Group(uniqid(), $data[Group::NAME], $status);

        return $group;
    }

    /**
     * @param $data
     * @throws ValidationError
     */
    private function validate_group_data($data) {
        if (!array_key_exists(Group::NAME, $data)) {
            throw new ValidationError("Field 'name' is required");
        }
    }
}