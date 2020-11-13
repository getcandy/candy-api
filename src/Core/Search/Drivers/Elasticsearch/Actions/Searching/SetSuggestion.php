<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\Searching;

use Elastica\Suggest;
use Elastica\Suggest\CandidateGenerator\DirectGenerator;
use Elastica\Suggest\Phrase;
use Lorisleiva\Actions\Action;

class SetSuggestion extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'query' => 'required',
            'term' => 'string',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Elastica\Query
     */
    public function handle()
    {
        // Did you mean...
        $phrase = new Phrase(
            'name',
            'name'
        );
        $phrase->setGramSize(3);
        $phrase->setSize(1);
        $phrase->setText($this->term);

        $generator = new DirectGenerator('name');
        $generator->setSuggestMode('always');
        $generator->setField('name');
        $phrase->addCandidateGenerator($generator);

        $phrase->setHighlight('<strong>', '</strong>');
        $suggest = new Suggest;
        $suggest->addSuggestion($phrase);

        $this->query->setSuggest($suggest);

        return $this->query;
    }
}
