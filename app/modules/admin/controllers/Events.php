<?php

/**
 * @copyright Eliel de Paula <dev@elieldepaula.com.br>
 * @license http://wpanel.org/license
 */

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Events class.
 * 
 * @author Eliel de Paula <dev@elieldepaula.com.br>
 */
class Events extends Authenticated_Controller
{

    /**
     * Class constructor.
     */
    function __construct()
    {
        $this->model_file = 'post';
        parent::__construct();
    }

    /**
     * Events list.
     */
    public function index()
    {
        $this->load->library('table');
        // Template da tabela
        $this->table->set_template(array('table_open' => '<table id="grid" class="table table-striped">'));
        $this->table->set_heading('#', 'Título', 'Data', 'Status', 'Ações');
        $query = $this->post->where('page', '2')->order_by('created_on', 'desc')->find_all();
        foreach ($query as $row)
        {
            $this->table->add_row(
                    $row->id, $row->title, datetime_for_user($row->created_on, false), status_post($row->status),
                    // Ícones de ações
                    div(array('class' => 'btn-group btn-group-xs')) .
                    anchor('admin/events/edit/' . $row->id, glyphicon('edit'), array('class' => 'btn btn-default')) .
                    '<button class="btn btn-default" onClick="return confirmar(\'' . site_url('admin/events/delete/' . $row->id) . '\');">' . glyphicon('trash') . '</button>' .
                    div(null, true)
            );
        }
        $this->set_var('listagem', $this->table->generate());
        $this->render();
    }

    /**
     * New event.
     */
    public function add()
    {
        $this->form_validation->set_rules('title', 'Título', 'required');
        $this->form_validation->set_rules('created', 'Data', 'required');
        if ($this->form_validation->run() == FALSE)
        {
            $this->render();
        } else
        {
            $data = array();
            $data['title'] = $this->input->post('title');
            $data['description'] = $this->input->post('description');
            $data['link'] = strtolower(url_title(convert_accented_characters($this->input->post('title'))));
            $data['content'] = $this->input->post('content');
            $data['tags'] = $this->input->post('tags');
            $data['status'] = $this->input->post('status');
            $data['created_on'] = datetime_for_mysql($this->input->post('created') . ' 12:00:00');
            $data['image'] = $this->wpanel->upload_media('capas', '*', 'userfile', date('YmdHis'));
            // Identifica se é uma página ou uma postagem
            // 0=post, 1=Página, 2=Agenda
            $data['page'] = '2';
            if ($this->post->insert($data))
                $this->set_message('Evento salvo com sucesso!', 'success', 'admin/events');
            else
                $this->set_message('Erro ao salvar o evento.', 'danger', 'admin/events');
        }
    }

    /**
     * Edit an event.
     * 
     * @param int $id
     */
    public function edit($id = null)
    {
        $this->form_validation->set_rules('title', 'Título', 'required');
        $this->form_validation->set_rules('created', 'Data', 'required');
        if ($this->form_validation->run() == FALSE)
        {
            if ($id == null)
                $this->set_message('Evento inexistente.', 'info', 'admin/events');
            $this->set_var('id', $id);
            $this->set_var('row', $this->post->find($id));
            $this->render();
        } else
        {
            $data = array();
            $data['title'] = $this->input->post('title');
            $data['description'] = $this->input->post('description');
            $data['link'] = strtolower(url_title(convert_accented_characters($this->input->post('title'))));
            $data['content'] = $this->input->post('content');
            $data['tags'] = $this->input->post('tags');
            $data['status'] = $this->input->post('status');
            $data['created_on'] = datetime_for_mysql($this->input->post('created') . ' 12:00:00');
            // Identifica se é uma página ou uma postagem
            // 0=post, 1=Página, 2=Agenda
            $data['page'] = '2';
            if ($this->input->post('alterar_imagem') == '1')
            {
                $postagem = $this->post->find($id);
                $this->wpanel->remove_media('capas/' . $postagem->image);
                $data['image'] = $this->wpanel->upload_media('capas', '*', 'userfile', date('YmdHis'));
            }
            if ($this->post->update($id, $data))
                $this->set_message('Evento salvo com sucesso!', 'success', 'admin/events');
            else
                $this->set_message('Erro ao salvar o evento.', 'danger', 'admin/events');
        }
    }

    /**
     * Delete an event.
     * 
     * @param int $id
     */
    public function delete($id = null)
    {
        if ($id == null)
            $this->set_message('Evento Inexistente!', 'info', 'admin/events');
        $this->load->model('post');
        $postagem = $this->post->find($id);
        $this->wpanel->remove_media('capas/' . $postagem->image);
        if ($this->post->delete($id))
            $this->set_message('Evento excluído com sucesso!', 'success', 'admin/events');
        else
            $this->set_message('Erro ao excluir o evento.', 'danger', 'admin/events');
    }

}
